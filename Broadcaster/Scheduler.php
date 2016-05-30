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
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, $twitchServer, $twitchKey)
    {
        $this->entityManager = $entityManager;
        $this->twitchServer = $twitchServer;
        $this->twitchKey = $twitchKey;
    }

    /**
     * Run streams that need to be running
     */
    public function applySchedule()
    {
        $broadcasting = $this->getCurrentBroadcasts();

        $broadcastRepository = $this->entityManager->getRepository('LiveBroadcastBundle:LiveBroadcast');
        $expr = Criteria::expr();
        $criterea = Criteria::create();

        $criterea->where($expr->andX(
            $expr->lte('startTimestamp', new \DateTime()),
            $expr->gte('endTimestamp', new \DateTime())
        ));

        /** @var LiveBroadcast[] $nowLive */
        $planned = $broadcastRepository->createQueryBuilder('lb')->addCriteria($criterea)->getQuery()->getResult();

        foreach($planned as $broadcast) {
            // @Todo Test if broadcast is already streaming
            $this->startBroadcast($broadcast);
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
        exec('ps -C ffmpeg -o pid=,args=', $output);

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

        $streamCommand = sprintf('ffmpeg %s %s -metadata broadcast_id=%d < /dev/null >/dev/null 2>/dev/null &', $streamInput, $streamOutput, $broadcast->getBroadcastId());
        exec($streamCommand);
    }

    /**
     * Kill a broadcast
     *
     * @param LiveBroadcast $broadcast
     */
    public function stopBroadcast(LiveBroadcast $broadcast)
    {
        // @Todo find broadcast PID by broadcast_id metatag
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
        preg_match('/broadcast_id=[\d]+/', $processString, $broadcast);
        if (is_array($broadcast) && is_string($broadcast[0])) {
            $broadcastId = end(explode('=', $broadcast[0]));
            return $broadcastId;
        }

        return null;
    }
}