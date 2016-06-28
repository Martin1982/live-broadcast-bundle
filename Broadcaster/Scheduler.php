<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Streams\InputFactory;
use Martin1982\LiveBroadcastBundle\Streams\OutputFactory;
use Psr\Log\LoggerInterface;

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
     * @param LoggerInterface            $logger
     */
    public function __construct(
        EntityManager $entityManager,
        SchedulerCommandsInterface $schedulerCommands,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->schedulerCommands = $schedulerCommands;
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

            if ($broadcast->getEndTimestamp() < new \DateTime()) {
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
        $output = $this->schedulerCommands->getRunningProcesses();

        foreach ($output as $runningBroadcast) {
            $runningItem = new RunningBroadcast(
                $this->schedulerCommands->getBroadcastId($runningBroadcast),
                $this->schedulerCommands->getProcessId($runningBroadcast),
                $this->schedulerCommands->getChannelId($runningBroadcast)
            );

            if ($runningItem->isValid()) {
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
            $inputProcessor = InputFactory::loadInputStream($broadcast);
            $outputProcessor = OutputFactory::loadOutput($channel);

            $streamInput = $inputProcessor->generateInputCmd();
            $streamOutput = $outputProcessor->generateOutputCmd();

            $this->logger->info(
                sprintf(
                    'Start broadcast %d (%s) on %d (%s).',
                    $broadcast->getBroadcastId(),
                    $broadcast->getName(),
                    $channel->getChannelId(),
                    $channel->getChannelName()
                )
            );
            $this->schedulerCommands->startProcess($streamInput, $streamOutput, array(
                'broadcast_id' => $broadcast->getBroadcastId(),
                'channel_id' => $channel->getChannelId(),
            ));
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
        $expr = Criteria::expr();
        $criterea = Criteria::create();

        $criterea->where($expr->andX(
            $expr->lte('startTimestamp', new \DateTime()),
            $expr->gte('endTimestamp', new \DateTime())
        ));

        $this->logger->debug('Get planned broadcasts');

        /* @var LiveBroadcast[] $nowLive */
        $this->plannedBroadcasts = $broadcastRepository->createQueryBuilder('lb')
            ->addCriteria($criterea)
            ->getQuery()
            ->getResult();

        return $this->plannedBroadcasts;
    }
}
