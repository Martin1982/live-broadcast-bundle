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
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Scheduler.
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
    protected $runningBroadcasts = array();

    /**
     * @var LiveBroadcast[]
     */
    protected $plannedBroadcasts = array();

    /**
     * Scheduler constructor.
     *
     * @param EntityManager              $entityManager
     * @param SchedulerCommandsInterface $schedulerCommands
     * @param StreamOutputService        $outputService
     * @param EventDispatcherInterface   $dispatcher
     * @param LoggerInterface            $logger
     */
    public function __construct(
        EntityManager $entityManager,
        SchedulerCommandsInterface $schedulerCommands,
        StreamOutputService $outputService,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->schedulerCommands = $schedulerCommands;
        $this->outputService = $outputService;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * Run streams that need to be running.
     */
    public function applySchedule()
    {
        $this->getRunningBroadcasts();
        $this->stopExpiredBroadcasts();
        $this->getPlannedBroadcasts();
        $this->startPlannedBroadcasts();

        $postBroadcastLoopEvent = new PostBroadcastLoopEvent();
        $this->dispatcher->dispatch(PostBroadcastLoopEvent::NAME, $postBroadcastLoopEvent);
    }

    /**
     * Start planned broadcasts if not already running.
     */
    public function startPlannedBroadcasts()
    {
        foreach ($this->plannedBroadcasts as $plannedBroadcast) {
            $this->startBroadcastOnChannels($plannedBroadcast);
        }

        $this->getRunningBroadcasts();
    }

    /**
     * @param LiveBroadcast $plannedBroadcast
     */
    public function startBroadcastOnChannels(LiveBroadcast $plannedBroadcast)
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
    public function stopExpiredBroadcasts()
    {
        $broadcastRepository = $this->entityManager->getRepository('LiveBroadcastBundle:LiveBroadcast');

        foreach ($this->runningBroadcasts as $runningBroadcast) {
            $broadcast = $broadcastRepository->find($runningBroadcast->getBroadcastId());

            if (!($broadcast instanceof LiveBroadcast)) {
                $this->logger->error(sprintf(
                    'Unable to stop broadcast %d, PID: %d not found in database.',
                    $runningBroadcast->getBroadcastId(),
                    $runningBroadcast->getProcessId()
                ));
                continue;
            }

            if ($broadcast->isStopOnEndTimestamp() &&
                $broadcast->getEndTimestamp() < new \DateTime()) {
                $this->logger->info(
                    sprintf(
                        'Stop broadcast %d (%s), PID: %d.',
                        $broadcast->getBroadcastId(),
                        $broadcast->getName(),
                        $runningBroadcast->getBroadcastId()
                    )
                );
                $this->schedulerCommands->stopProcess($runningBroadcast->getProcessId());
            }
        }

        $this->getRunningBroadcasts();
    }

    /**
     * Retrieve what is broadcasting.
     *
     * @return RunningBroadcast[]
     */
    public function getRunningBroadcasts()
    {
        $this->runningBroadcasts = array();
        $this->logger->debug('Get running broadcasts');
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
    public function startBroadcast(LiveBroadcast $broadcast, BaseChannel $channel)
    {
        try {
            $input = $broadcast->getInput()->generateInputCmd();
            $output = $this->outputService->getOutputInterface($channel);

            $preBroadcastEvent = new PreBroadcastEvent($broadcast, $output);
            $this->dispatcher->dispatch(PreBroadcastEvent::NAME, $preBroadcastEvent);

            $this->logger->info(
                sprintf(
                    'Start broadcast %d (%s) on %d (%s).',
                    $broadcast->getBroadcastId(),
                    $broadcast->getName(),
                    $channel->getChannelId(),
                    $channel->getChannelName()
                )
            );

            $this->logger->info(sprintf('Starting broadcast with %s', $output->generateOutputCmd()));
            $this->schedulerCommands->startProcess($input, $output->generateOutputCmd(), array(
                'broadcast_id' => $broadcast->getBroadcastId(),
                'channel_id' => $channel->getChannelId(),
            ));

            $postBroadcastEvent = new PostBroadcastEvent($broadcast, $output);
            $this->dispatcher->dispatch(PostBroadcastEvent::NAME, $postBroadcastEvent);
        } catch (LiveBroadcastException $ex) {
            $this->logger->error(
                sprintf(
                    'Could not start broadcast %d (%s): %s',
                    $broadcast->getBroadcastId(),
                    $broadcast->getName(),
                    $ex->getMessage()
                )
            );
        }
    }

    /**
     * Get the planned broadcast items.
     *
     * @return LiveBroadcast[]
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    protected function getPlannedBroadcasts()
    {
        $broadcastRepository = $this->entityManager->getRepository('LiveBroadcastBundle:LiveBroadcast');
        $this->logger->debug('Get planned broadcasts');
        $this->plannedBroadcasts = $broadcastRepository->getPlannedBroadcasts();

        return $this->plannedBroadcasts;
    }
}
