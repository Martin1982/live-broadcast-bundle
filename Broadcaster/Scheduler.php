<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastLoopEvent;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Event\SwitchMonitorEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\StreamInputService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Scheduler
 * @package Martin1982\LiveBroadcastBundle\Broadcaster
 */
class Scheduler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SchedulerCommandsInterface
     */
    protected $schedulerCommands;

    /**
     * @var StreamOutputService
     */
    protected $outputService;

    /**
     * @var StreamInputService
     */
    protected $inputService;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var RunningBroadcast[]
     */
    protected $runningBroadcasts = [];

    /**
     * @var LiveBroadcast[]
     */
    protected $plannedBroadcasts = [];

    /**
     * Scheduler constructor.
     *
     * @param EntityManager $entityManager
     * @param SchedulerCommandsInterface $schedulerCommands
     * @param StreamOutputService $outputService
     * @param StreamInputService $inputService
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManager $entityManager,
        SchedulerCommandsInterface $schedulerCommands,
        StreamOutputService $outputService,
        StreamInputService $inputService,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->schedulerCommands = $schedulerCommands;
        $this->outputService = $outputService;
        $this->inputService = $inputService;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * Run streams that need to be running.
     *
     * @throws LiveBroadcastException
     */
    public function applySchedule()
    {
        $this->updateRunningBroadcasts();
        $this->stopExpiredBroadcasts();
        $this->getPlannedBroadcasts();
        $this->startPlannedBroadcasts();

        $postBroadcastLoopEvent = new PostBroadcastLoopEvent();
        $this->dispatcher->dispatch(PostBroadcastLoopEvent::NAME, $postBroadcastLoopEvent);
    }

    /**
     * Start planned broadcasts if not already running.
     */
    protected function startPlannedBroadcasts()
    {
        foreach ($this->plannedBroadcasts as $plannedBroadcast) {
            $this->startBroadcastOnChannels($plannedBroadcast);
        }

        $this->updateRunningBroadcasts();
    }

    /**
     * @param LiveBroadcast $plannedBroadcast
     */
    protected function startBroadcastOnChannels(LiveBroadcast $plannedBroadcast)
    {
        $channels = $plannedBroadcast->getOutputChannels();

        foreach ($channels as $channel) {
            $isChannelBroadcasting = false;

            foreach ($this->runningBroadcasts as $runningBroadcast) {
                if ($runningBroadcast->isBroadcasting($plannedBroadcast, $channel)) {
                    $isChannelBroadcasting = true;
                }

                if ($runningBroadcast->isMonitor()) {
                    $switchMonitorEvent = new SwitchMonitorEvent($runningBroadcast, $plannedBroadcast, $channel);
                    $this->dispatcher->dispatch(SwitchMonitorEvent::NAME, $switchMonitorEvent);
                }
            }

            if (!$isChannelBroadcasting) {
                $this->startBroadcast($plannedBroadcast, $channel);
            }
        }
    }

    /**
     * Stop running broadcasts that have expired.
     */
    protected function stopExpiredBroadcasts()
    {
        $broadcastRepository = $this->entityManager->getRepository('LiveBroadcastBundle:LiveBroadcast');

        foreach ($this->runningBroadcasts as $runningBroadcast) {
            $broadcast = $broadcastRepository->find($runningBroadcast->getBroadcastId());

            if (!($broadcast instanceof LiveBroadcast)) {
                $this->logger->error(
                    'Unable to stop broadcast, PID not found in database',
                    [
                        'broadcast_id' => $runningBroadcast->getBroadcastId(),
                        'pid' => $runningBroadcast->getProcessId(),
                    ]
                );
                continue;
            }

            if ($broadcast->isStopOnEndTimestamp() &&
                $broadcast->getEndTimestamp() < new \DateTime()) {
                $this->logger->info(
                    'Stop broadcast',
                    [
                        'broadcast_id' => $broadcast->getBroadcastId(),
                        'broadcast_name' => $broadcast->getName(),
                        'pid' => $runningBroadcast->getProcessId(),
                    ]
                );
                $this->schedulerCommands->stopProcess($runningBroadcast->getProcessId());
            }
        }

        $this->updateRunningBroadcasts();
    }

    /**
     * Retrieve what is broadcasting.
     *
     * @return RunningBroadcast[]
     */
    protected function updateRunningBroadcasts()
    {
        $this->runningBroadcasts = [];
        $this->logger->debug('Retrieve running broadcasts');
        $processStrings = $this->schedulerCommands->getRunningProcesses();

        foreach ($processStrings as $processString) {
            $runningItem = new RunningBroadcast(
                $this->schedulerCommands->getBroadcastId($processString),
                $this->schedulerCommands->getProcessId($processString),
                $this->schedulerCommands->getChannelId($processString),
                $this->schedulerCommands->getEnvironment($processString),
                $this->schedulerCommands->isMonitorStream($processString)
            );

            if ($runningItem->isValid($this->schedulerCommands->getKernelEnvironment())) {
                $this->runningBroadcasts[] = $runningItem;
            }
        }

        return $this->runningBroadcasts;
    }

    /**
     * Initiate a new broadcast.
     *
     * @param LiveBroadcast $broadcast
     * @param BaseChannel   $channel
     */
    protected function startBroadcast(LiveBroadcast $broadcast, BaseChannel $channel)
    {
        try {
            $input = $this->inputService->getInputInterface($broadcast->getInput());
            $output = $this->outputService->getOutputInterface($channel);

            $preBroadcastEvent = new PreBroadcastEvent($broadcast, $output);
            $this->dispatcher->dispatch(PreBroadcastEvent::NAME, $preBroadcastEvent);

            $this->logger->info(
                'Start broadcast',
                [
                    'broadcast_id' => $broadcast->getBroadcastId(),
                    'broadcast_name' => $broadcast->getName(),
                    'channel_id' => $channel->getChannelId(),
                    'channel_name' => $channel->getChannelName(),
                    'input_cmd' => $input->generateInputCmd(),
                    'output_cmd' => $output->generateOutputCmd(),
                ]
            );

            $this->schedulerCommands->setIsLoopable($broadcast->isStopOnEndTimestamp());
            $this->schedulerCommands->startProcess($input->generateInputCmd(), $output->generateOutputCmd(), [
                'broadcast_id' => $broadcast->getBroadcastId(),
                'channel_id' => $channel->getChannelId(),
            ]);

            $postBroadcastEvent = new PostBroadcastEvent($broadcast, $output);
            $this->dispatcher->dispatch(PostBroadcastEvent::NAME, $postBroadcastEvent);
        } catch (LiveBroadcastException $ex) {
            $this->logger->error(
                'Could not start broadcast',
                [
                    'broadcast_id' => $broadcast->getBroadcastId(),
                    'broadcast_name' => $broadcast->getName(),
                    'exception' => $ex->getMessage(),
                ]
            );
        }
    }

    /**
     * Get the planned broadcast items.
     *
     * @return LiveBroadcast[]
     *
     * @throws LiveBroadcastException
     */
    protected function getPlannedBroadcasts()
    {
        $broadcastRepository = $this->entityManager->getRepository('LiveBroadcastBundle:LiveBroadcast');
        $this->logger->debug('Get planned broadcasts');
        $this->plannedBroadcasts = $broadcastRepository->getPlannedBroadcasts();

        return $this->plannedBroadcasts;
    }
}
