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
     * @var string
     */
    protected $environment;

    /**
     * Scheduler constructor.
     * @param EntityManager $entityManager
     * @param string        $twitchServer
     * @param string        $twitchKey
     * @param string        $environment
     */
    public function __construct(EntityManager $entityManager, $twitchServer, $twitchKey, $environment)
    {
        $this->entityManager = $entityManager;
        $this->twitchServer = $twitchServer;
        $this->twitchKey = $twitchKey;
        $this->environment = $environment;
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
                $this->stopBroadcast($running['pid']);
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
        exec('/bin/ps -C ffmpeg -o pid=,args=', $output);

        foreach($output as $runningBroadcast) {
            $runningItem = array(
                'pid'         => $this->getPid($runningBroadcast),
                'broadcastId' => $this->getBroadcastId($runningBroadcast),
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

        $streamCommand = sprintf('ffmpeg %s %s -metadata env=%s -metadata broadcast_id=%d >/dev/null 2>&1 &', $streamInput, $streamOutput, $this->environment, $broadcast->getBroadcastId());
        exec($streamCommand);
    }

    /**
     * Kill a broadcast
     *
     * @param int           $pid
     */
    public function stopBroadcast($pid)
    {
        exec(sprintf("kill %d", $pid));
    }

    /**
     * Get the PID for the broadcast
     *
     * @param $processString
     * @return int|null
     */
    protected function getPid($processString)
    {
        preg_match('/^[\d]+/', $processString, $pid);
        if (count($pid) && is_numeric($pid[0])) {
            return (int)$pid[0];
        }

        return null;
    }

    /**
     * Get the currently playing broadcast
     *
     * @param $processString
     * @return string|null
     */
    protected function getBroadcastId($processString)
    {
        preg_match('/env='.$this->environment.' -metadata broadcast_id=[\d]+/', $processString, $broadcast);
        if (is_array($broadcast) && is_string($broadcast[0])) {
            $broadcastDetails = explode('=', $broadcast[0]);
            return end($broadcastDetails);
        }

        return null;
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
