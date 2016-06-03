<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Streams\Input\File;
use Martin1982\LiveBroadcastBundle\Streams\Output\Twitch;

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
     * @var string
     */
    protected $twitchServer;

    /**
     * @var string
     */
    protected $twitchKey;

    /**
     * @var SchedulerCommandsInterface
     */
    protected $schedulerCommands;

    /**
     * Scheduler constructor.
     * @param EntityManager              $entityManager
     * @param SchedulerCommandsInterface $schedulerCommands
     * @param string                     $twitchServer
     * @param string                     $twitchKey
     */
    public function __construct(EntityManager $entityManager, SchedulerCommandsInterface $schedulerCommands, $twitchServer, $twitchKey)
    {
        $this->entityManager = $entityManager;
        $this->schedulerCommands = $schedulerCommands;
        $this->twitchServer = $twitchServer;
        $this->twitchKey = $twitchKey;
    }

    /**
     * Run streams that need to be running
     */
    public function applySchedule()
    {
        $broadcastRepository = $this->entityManager->getRepository('LiveBroadcastBundle:LiveBroadcast');
        $broadcasting = $this->getCurrentBroadcasts();
        $plannedBroadcasts = $this->getPlannedBroadcasts();
        $runningIds = array();

        // Stop running broadcasts that have expired
        foreach ($broadcasting as $running) {
            $broadcast = $broadcastRepository->find($running['broadcastId']);

            if ($broadcast->getEndTimestamp() < new \DateTime()) {
                $this->schedulerCommands->stopProcess($running['pid']);
            }

            array_push($runningIds, $running['broadcastId']);
        }

        // Start planned broadcasts if not already running
        foreach($plannedBroadcasts as $planned) {
            $plannedId = $planned->getBroadcastId();

            if (!in_array($plannedId, $runningIds)) {
                $broadcast = $broadcastRepository->find($plannedId);
                $this->startBroadcast($broadcast);
            }
        }
    }

    /**
     * Retrieve what is broadcasting
     *
     * @return array
     */
    public function getCurrentBroadcasts()
    {
        $running = array();
        $output = $this->schedulerCommands->getRunningProcesses();

        foreach($output as $runningBroadcast) {
            $runningItem = array(
                'pid'         => $this->schedulerCommands->getProcessId($runningBroadcast),
                'broadcastId' => $this->schedulerCommands->getBroadcastId($runningBroadcast),
            );

            if (!empty($runningItem['pid']) && !empty($runningItem['broadcastId'])) {
                array_push($running, $runningItem);
            }
        }

        return $running;
    }

    /**
     * Initiate a new broadcast
     *
     * @param LiveBroadcast $broadcast
     */
    public function startBroadcast(LiveBroadcast $broadcast)
    {
        // @TODO Add factory when supporting other inputs
        $inputProcessor = new File($broadcast);
        // @TODO Add factory when supporting other outputs
        $outputProcessor = new Twitch($this->twitchServer, $this->twitchKey);

        $streamInput = $inputProcessor->generateInputCmd();
        $streamOutput = $outputProcessor->generateOutputCmd();

        $this->schedulerCommands->startProcess($streamInput, $streamOutput, array('broadcast_id' => $broadcast->getBroadcastId()));
    }

    /**
     * Get the planned broadcast items
     *
     * @return LiveBroadcast[]
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

        /** @var LiveBroadcast[] $nowLive */
        return $broadcastRepository->createQueryBuilder('lb')->addCriteria($criterea)->getQuery()->getResult();
    }
}
