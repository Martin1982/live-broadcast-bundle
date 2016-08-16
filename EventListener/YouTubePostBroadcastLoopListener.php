<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaMonitorStream;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastLoopEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputMonitorStream;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class YouTubePostBroadcastLoopListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
 */
class YouTubePostBroadcastLoopListener implements EventSubscriberInterface
{
    /**
     * @var SchedulerCommandsInterface
     */
    protected $commands;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var YouTubeApiService
     */
    protected $youTubeApiService;

    /** @var KernelInterface  */
    protected $kernel;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * YouTubePostBroadcastLoopListener constructor.
     * @param EntityManager $entityManager
     * @param SchedulerCommandsInterface $commands
     * @param YouTubeApiService $youTubeApiService
     * @param KernelInterface $kernel
     * @param GoogleRedirectService $redirectService
     * @param LoggerInterface $logger
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function __construct(
        EntityManager $entityManager,
        SchedulerCommandsInterface $commands,
        YouTubeApiService $youTubeApiService,
        KernelInterface $kernel,
        GoogleRedirectService $redirectService,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->commands = $commands;
        $this->youTubeApiService = $youTubeApiService;
        $this->kernel = $kernel;
        $this->logger = $logger;

        $redirectUri = $redirectService->getOAuthRedirectUrl();
        $this->youTubeApiService->initApiClients($redirectUri);
    }

    /**
     * Get planned streams which aren't live yet on the monitor
     *
     * @param PostBroadcastLoopEvent $event
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function onPostBroadcastLoop(PostBroadcastLoopEvent $event)
    {
        $entityManager = $this->entityManager;
        $eventRepository = $entityManager->getRepository('LiveBroadcastBundle:Metadata\YouTubeEvent');

        $runningProcesses = $this->commands->getRunningProcesses();
        $testableEvents = $eventRepository->getTestableEvents();

        foreach ($testableEvents as $testableEvent) {
            $this->updateEventState($testableEvent);

            if ($testableEvent->getLastKnownState() >= YouTubeEvent::STATE_LOCAL_TESTING) {
                continue;
            }

            if (!$this->hasRunningMonitorStream($testableEvent, $runningProcesses)) {
                $this->startMonitorStream($testableEvent);
            }

            $this->transitionState($testableEvent);
        }

        $this->cleanMonitorStreams();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(PostBroadcastLoopEvent::NAME => 'onPostBroadcastLoop');
    }

    /**
     * @param YouTubeEvent $testableEvent
     */
    protected function updateEventState(YouTubeEvent $testableEvent)
    {
        $remoteState = $this->youTubeApiService->getBroadcastStatus(
            $testableEvent->getBroadcast(),
            $testableEvent->getChannel()
        );
        $convertedState = $testableEvent->getLocalStateByRemoteState($remoteState);
        $testableEvent->setLastKnownState($convertedState);

        $this->entityManager->persist($testableEvent);
        $this->entityManager->flush();
    }

    /**
     * @param YouTubeEvent $event
     * @param RunningBroadcast[] $runningProcesses
     * @return bool
     */
    protected function hasRunningMonitorStream(YouTubeEvent $event, $runningProcesses)
    {
        $broadcast = $event->getBroadcast();
        $channel = $event->getChannel();
        $streamFound = false;

        foreach ($runningProcesses as $processString) {
            $process = $this->createRunningProcess($processString);

            if ($process->getBroadcastId() === $broadcast->getBroadcastId() &&
                $process->getChannelId() === $channel->getChannelId()
            ) {
                $streamFound = true;
            }
        }

        return $streamFound;
    }

    /**
     * Start a monitor stream with a placeholder image
     *
     * @param YouTubeEvent $event
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    protected function startMonitorStream(YouTubeEvent $event)
    {
        $placeholderImage = $this->kernel->locateResource('@LiveBroadcastBundle') . '/Resources/images/placeholder.png';

        $inputMedia = new MediaMonitorStream();
        $inputMedia->setMonitorImage($placeholderImage);

        $inputService = new InputMonitorStream();
        $inputService->setMedia($inputMedia);

        $streamUrl = $this->youTubeApiService->getStreamUrl($event->getBroadcast(), $event->getChannel());

        $outputService = new OutputYouTube();
        $outputService->setChannel($event->getChannel());
        $outputService->setStreamUrl($streamUrl);

        $metadata = array(
            'broadcast_id' => $event->getBroadcast()->getBroadcastId(),
            'channel_id' => $event->getChannel()->getChannelId(),
            'monitor_stream' => 'yes',
        );

        try {
            $this->logger->info('YouTube start monitor stream, broadcast id: '. $metadata['broadcast_id']);
            $this->commands->startProcess(
                $inputService->generateInputCmd(),
                $outputService->generateOutputCmd(),
                $metadata
            );
        } catch (LiveBroadcastOutputException $e) {
            return;
        }
    }

    /**
     * Clean up running monitor streams
     */
    protected function cleanMonitorStreams()
    {
        $runningStreams = $this->commands->getRunningProcesses();
        
        foreach ($runningStreams as $streamCmd) {
            $process = $this->createRunningProcess($streamCmd);

            if ($process->isMonitor() === false) {
                continue;
            }

            $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YouTubeEvent');
            $event = $eventRepository->find($process->getBroadcastId());

            if ($event && $event->getLastKnownState() >= YouTubeEvent::STATE_LOCAL_COMPLETE) {
                $this->commands->stopProcess($process->getProcessId());
            }
        }
    }

    /**
     * Try to transition the state of the stream
     *
     * @param YouTubeEvent $event
     */
    protected function transitionState(YouTubeEvent $event)
    {
        $liveService = $this->youTubeApiService;
        $liveService->transitionState($event->getBroadcast(), $event->getChannel(), YouTubeEvent::STATE_REMOTE_TESTING);
    }

    /**
     * @param string $processString
     * @return RunningBroadcast
     */
    protected function createRunningProcess($processString)
    {
        return new RunningBroadcast(
            $this->commands->getBroadcastId($processString),
            $this->commands->getProcessId($processString),
            $this->commands->getChannelId($processString),
            $this->commands->getEnvironment($processString),
            $this->commands->isMonitorStream($processString)
        );
    }
}
