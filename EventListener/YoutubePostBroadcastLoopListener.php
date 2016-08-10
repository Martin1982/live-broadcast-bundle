<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YoutubeEvent;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastLoopEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYoutube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeLiveService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

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
     */
    public function __construct(
        EntityManager $entityManager,
        SchedulerCommandsInterface $commands,
        YouTubeLiveService $youTubeLiveService,
        KernelInterface $kernel
    ) {
        $this->entityManager = $entityManager;
        $this->commands = $commands;
        $this->youtubeLiveService = $youTubeLiveService;
        $this->kernel = $kernel;
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
        $testableEvents = $eventRepository->getTestableEvents();

        if (!count($testableEvents)) {
            return;
        }

        $runningProcesses = $this->commands->getRunningProcesses();

        foreach ($testableEvents as $testableEvent) {
            if (!$this->hasRunningTestStream($testableEvent, $runningProcesses)) {
                $this->startTestStream($testableEvent);
            }
            $this->transitionState($testableEvent);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(PostBroadcastLoopEvent::NAME => 'onPostBroadcastLoop');
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

        foreach ($runningProcesses as $process) {
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
        $placeholderImage = $this->kernel->locateResource('@LiveBroadcastBundle') . '/Resources/images/placeholder.jpg';

        $input = sprintf(
            '-re -framerate 1/%d -i %s',
            $this->testDuration,
            escapeshellarg($placeholderImage)
        );

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
            $this->commands->startProcess($input, $outputService->generateOutputCmd(), $metadata);
        } catch (LiveBroadcastException $e) {
            return;
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
}
