<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Streams\OutputFactory;
use Martin1982\LiveBroadcastBundle\Streams\Input\File;

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
     * @var RunningBroadcast[]
     */
    protected $runningBroadcasts = array();

    /**
     * @var LiveBroadcast[]
     */
    protected $plannedBroadcasts = array();

    /**
     * Scheduler constructor.
     * @param EntityManager              $entityManager
     * @param SchedulerCommandsInterface $schedulerCommands
     */
    public function __construct(EntityManager $entityManager, SchedulerCommandsInterface $schedulerCommands)
    {
        $this->entityManager = $entityManager;
        $this->schedulerCommands = $schedulerCommands;
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
     * Start planned broadcasts if not already running
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
                if (
                    (int) $runningBroadcast->getBroadcastId() === (int) $plannedBroadcast->getBroadcastId() &&
                    (int) $runningBroadcast->getChannelId() === (int) $channel->getChannelId()
                ) {
                    $isChannelBroadcasting = true;
                }
            }

            if (!$isChannelBroadcasting) {
                $this->startBroadcast($plannedBroadcast, $channel);
            }
        }
    }
    
    
    /**
     * Stop running broadcasts that have expired
     */
    public function stopExpiredBroadcasts()
    {
        $broadcastRepository = $this->entityManager->getRepository('LiveBroadcastBundle:LiveBroadcast');

        foreach ($this->runningBroadcasts as $runningBroadcast) {
            $broadcast = $broadcastRepository->find($runningBroadcast->getBroadcastId());

            if ($broadcast->getEndTimestamp() < new \DateTime()) {
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
        // @TODO Add factory when supporting other inputs
        $inputProcessor = new File($broadcast);
        $outputProcessor = OutputFactory::loadOutput($channel);

        $streamInput = $inputProcessor->generateInputCmd();
        $streamOutput = $outputProcessor->generateOutputCmd();

        $this->schedulerCommands->startProcess($streamInput, $streamOutput, array(
            'broadcast_id' => $broadcast->getBroadcastId(),
            'channel_id'   => $channel->getChannelId(),
        ));
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

        /* @var LiveBroadcast[] $nowLive */
        $this->plannedBroadcasts = $broadcastRepository->createQueryBuilder('lb')
            ->addCriteria($criterea)
            ->getQuery()
            ->getResult();

        return $this->plannedBroadcasts;
    }
}
