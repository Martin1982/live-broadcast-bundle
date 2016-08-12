<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Input\InputMonitorStream;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YoutubeEvent;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastLoopEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYoutube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeLiveService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Router;

/**
 * Class YoutubePostBroadcastLoopListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
 */
class YoutubePostBroadcastLoopListener implements EventSubscriberInterface
{
    /**
     * Test video duration in seconds
     * @var int
     */
    public $testDuration = 300;

    /**
     * @var SchedulerCommandsInterface
     */
    protected $commands;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var YouTubeLiveService
     */
    protected $youtubeLiveService;

    /** @var KernelInterface  */
    protected $kernel;

    /**
     * YoutubePostBroadcastLoopListener constructor.
     * @param EntityManager $entityManager
     * @param SchedulerCommandsInterface $commands
     * @param YouTubeLiveService $youTubeLiveService
     * @param KernelInterface $kernel
     * @param Router $router
     * @param $redirectRoute
     */
    public function __construct(
        EntityManager $entityManager,
        SchedulerCommandsInterface $commands,
        YouTubeLiveService $youTubeLiveService,
        KernelInterface $kernel,
        Router $router,
        $redirectRoute
    ) {
        $this->entityManager = $entityManager;
        $this->commands = $commands;
        $this->youtubeLiveService = $youTubeLiveService;
        $this->kernel = $kernel;

        $redirectUri = $router->generate(
            $redirectRoute,
            array(),
            Router::ABSOLUTE_URL
        );
        $this->youtubeLiveService->initApiClients($redirectUri);
    }

    /**
     * Get planned streams which aren't live yet on the monitor
     *
     * @param PostBroadcastLoopEvent $event
     */
    public function onPostBroadcastLoop(PostBroadcastLoopEvent $event)
    {
        $entityManager = $this->entityManager;
        $eventRepository = $entityManager->getRepository('LiveBroadcastBundle:Metadata\YoutubeEvent');

        $runningProcesses = $this->commands->getRunningProcesses();
        $testableEvents = $eventRepository->getTestableEvents();

        foreach ($testableEvents as $testableEvent) {
            $this->updateEventState($testableEvent);

            if ($testableEvent->getLastKnownState() >= YoutubeEvent::STATE_LOCAL_TESTING) {
                continue;
            }

            if (!$this->hasRunningTestStream($testableEvent, $runningProcesses)) {
                $this->startTestStream($testableEvent);
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
     * @param YoutubeEvent $testableEvent
     */
    protected function updateEventState(YoutubeEvent $testableEvent)
    {
        $remoteState = $this->youtubeLiveService->getBroadcastStatus(
            $testableEvent->getBroadcast(),
            $testableEvent->getChannel()
        );
        $convertedState = $testableEvent->getLocalStateByRemoteState($remoteState);
        $testableEvent->setLastKnownState($convertedState);

        $this->entityManager->persist($testableEvent);
        $this->entityManager->flush();
    }

    /**
     * @param YoutubeEvent $event
     * @param RunningBroadcast[] $runningProcesses
     * @return bool
     */
    protected function hasRunningTestStream(YoutubeEvent $event, $runningProcesses)
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
     * Start a test stream with a placeholder image
     *
     * @param YoutubeEvent $event
     */
    protected function startTestStream(YoutubeEvent $event)
    {
        $placeholderImage = $this->kernel->locateResource('@LiveBroadcastBundle') . '/Resources/images/placeholder.png';

        $inputService = new InputMonitorStream();
        $inputService->setMonitorImage($placeholderImage);

        $streamUrl = $this->youtubeLiveService->getStreamUrl($event->getBroadcast(), $event->getChannel());

        $outputService = new OutputYoutube();
        $outputService->setChannel($event->getChannel());
        $outputService->setStreamUrl($streamUrl);

        $metadata = array(
            'broadcast_id' => $event->getBroadcast()->getBroadcastId(),
            'channel_id' => $event->getChannel()->getChannelId(),
            'monitor_stream' => 'yes',
        );

        try {
            $this->commands->startProcess(
                $inputService->generateInputCmd(),
                $outputService->generateOutputCmd(),
                $metadata
            );
        } catch (LiveBroadcastException $e) {
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

            $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YoutubeEvent');
            $event = $eventRepository->find($process->getBroadcastId());

            if ($event && $event->getLastKnownState() >= YoutubeEvent::STATE_LOCAL_COMPLETE) {
                $this->commands->stopProcess($process->getProcessId());
            }
        }
    }

    /**
     * Try to transition the state of the stream
     *
     * @param YoutubeEvent $event
     */
    protected function transitionState(YoutubeEvent $event)
    {
        $liveService = $this->youtubeLiveService;
        $liveService->transitionState($event->getBroadcast(), $event->getChannel(), YoutubeEvent::STATE_REMOTE_TESTING);
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
