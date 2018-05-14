<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Martin1982\LiveBroadcastBundle\Service\BroadcastStarter;
use Psr\Log\LoggerInterface;

/**
 * Class Scheduler
 */
class Scheduler
{
    /**
     * @var BroadcastStarter
     */
    protected $starter;

    /**
     * @var BroadcastManager
     */
    protected $broadcastManager;

    /**
     * @var SchedulerCommandsInterface
     */
    protected $schedulerCommands;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Scheduler constructor
     *
     * @param BroadcastStarter           $starter
     * @param BroadcastManager           $broadcastManager
     * @param SchedulerCommandsInterface $schedulerCommands
     * @param LoggerInterface            $logger
     *
     * phpcs:disable Symfony.Functions.Arguments.Invalid
     */
    public function __construct(BroadcastStarter $starter, BroadcastManager $broadcastManager, SchedulerCommandsInterface $schedulerCommands, LoggerInterface $logger)
    {
        $this->starter = $starter;
        $this->broadcastManager = $broadcastManager;
        $this->schedulerCommands = $schedulerCommands;
        $this->logger = $logger;
    }

    /**
     * Run streams that need to be running.
     *
     * @throws LiveBroadcastException
     */
    public function applySchedule(): void
    {
        $this->broadcastManager->keepConnectionAlive();
        $this->stopExpiredBroadcasts();
        $this->startPlannedBroadcasts();
        $this->sendEndSignals();
    }

    /**
     * Stop running broadcasts that have expired.
     */
    protected function stopExpiredBroadcasts(): void
    {
        $broadcastRepository = $this->broadcastManager->getBroadcastsRepository();

        foreach ($this->getRunningBroadcasts() as $runningBroadcast) {
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

            $isPastEndTime = $broadcast->getEndTimestamp() < new \DateTime();
            if ($isPastEndTime && $broadcast->isStopOnEndTimestamp()) {
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
    }

    /**
     * Start planned broadcasts if not already running.
     *
     * @throws LiveBroadcastException
     */
    protected function startPlannedBroadcasts(): void
    {
        $planned = $this->broadcastManager->getPlannedBroadcasts();

        foreach ($planned as $plannedBroadcast) {
            $this->startBroadcastOnChannels($plannedBroadcast);
        }
    }

    /**
     * @param LiveBroadcast $plannedBroadcast
     */
    protected function startBroadcastOnChannels(LiveBroadcast $plannedBroadcast): void
    {
        $channels = $plannedBroadcast->getOutputChannels();

        foreach ($channels as $channel) {
            $isBroadcasting = $this->isBroadcasting($plannedBroadcast, $channel);

            if ($isBroadcasting) {
                continue;
            }

            try {
                $this->starter->startBroadcast($plannedBroadcast, $channel);
            } catch (\Throwable $exception) {
                $this->logger->error(
                    'Could not start broadcast',
                    [
                        'broadcast_id' => $plannedBroadcast->getBroadcastId(),
                        'broadcast_name' => $plannedBroadcast->getName(),
                        'exception' => $exception->getMessage(),
                    ]
                );
            }

            $infoMessage = sprintf('Started broadcast \'%s\' on \'%s\'', $plannedBroadcast->getName(), $channel->getChannelName());
            $this->logger->info($infoMessage);
        }
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @return bool
     */
    protected function isBroadcasting(LiveBroadcast $broadcast, AbstractChannel $channel): bool
    {
        $isBroadcasting = false;

        foreach ($this->getRunningBroadcasts() as $runningBroadcast) {
            $sameChannel = $runningBroadcast->getChannelId() === $channel->getChannelId();
            $sameBroadcast = $runningBroadcast->getBroadcastId() === $broadcast->getBroadcastId();

            if ($sameBroadcast && $sameChannel) {
                $isBroadcasting = true;
            }
        }

        return $isBroadcasting;
    }

    /**
     * Retrieve what is broadcasting from the process list
     *
     * @return RunningBroadcast[]
     */
    protected function getRunningBroadcasts(): array
    {
        $runningBroadcasts = [];
        $this->logger->debug('Retrieve running broadcasts');
        $processStrings = $this->schedulerCommands->getRunningProcesses();

        foreach ($processStrings as $processString) {
            $runningItem = new RunningBroadcast(
                $this->schedulerCommands->getBroadcastId($processString),
                $this->schedulerCommands->getProcessId($processString),
                $this->schedulerCommands->getChannelId($processString),
                $this->schedulerCommands->getEnvironment($processString)
            );

            if ($runningItem->isValid($this->schedulerCommands->getKernelEnvironment())) {
                $runningBroadcasts[] = $runningItem;
            }
        }

        return $runningBroadcasts;
    }

    /**
     * Send end signals to channels where broadcasts ended
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    protected function sendEndSignals(): void
    {
        $repository = $this->broadcastManager->getEventsRepository();
        /** @var StreamEvent[] $activeEvents */
        $activeEvents = $repository->findBy(['endSignalSent' => false]);

        foreach ($activeEvents as $event) {
            $needsEndSignal = $this->needsEndSignal($event);

            if (true === $needsEndSignal) {
                $this->broadcastManager->sendEndSignal($event);
            }
        }
    }

    /**
     * @param StreamEvent $event
     *
     * @return bool
     */
    protected function needsEndSignal(StreamEvent $event): bool
    {
        $needsEndSignal = false;
        $broadcast = $event->getBroadcast();
        $channel = $event->getChannel();

        if (!$broadcast || !$channel) {
            return false;
        }

        $hasStarted = $broadcast->getStartTimestamp() < new \DateTime();

        if (!$hasStarted) {
            return false;
        }

        $willBeStopped = $broadcast->isStopOnEndTimestamp();
        $hasPassedEndTime = $broadcast->getEndTimestamp() < new \DateTime();
        $isRunning = $this->isRunning($broadcast, $channel);

        if ($willBeStopped && $hasPassedEndTime) {
            $needsEndSignal = true;
        }

        if (!$willBeStopped && !$isRunning) {
            $needsEndSignal = true;
        }

        return $needsEndSignal;
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @return bool
     */
    protected function isRunning(LiveBroadcast $broadcast, AbstractChannel $channel): bool
    {
        $isRunning = false;

        foreach ($this->getRunningBroadcasts() as $runningBroadcast) {
            $runningBroadcastId = (string) $runningBroadcast->getBroadcastId();
            $runningChannelId   = (string) $runningBroadcast->getChannelId();
            $broadcastId        = (string) $broadcast->getBroadcastId();
            $channelId          = (string) $channel->getChannelId();

            $isBroadcastRunning = $runningBroadcastId === $broadcastId;
            $isRunningOnChannel = $runningChannelId === $channelId;

            if ($isBroadcastRunning && $isRunningOnChannel) {
                $isRunning = true;
            }
        }

        return $isRunning;
    }
}
