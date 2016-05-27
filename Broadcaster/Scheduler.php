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
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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

echo count($planned) . "\n";
        foreach($planned as $broadcast) {
            // @Todo Test if broadcast is already streaming
            $this->startBroadcast($broadcast);
        }
    }

    public function getCurrentBroadcasts()
    {
        exec('ps -C ffmpeg | grep broadcast_id', $output);
        foreach($output as $runningBroadcast) {
            echo $runningBroadcast . "\n";
        }

        // @Todo fill a broadcasts collection
        return array();
    }

    public function startBroadcast(LiveBroadcast $broadcast)
    {
        // @Todo use a broadcast_id metatag to match the schedule
        $inputProcessor = new File($broadcast);
        $outputProcessor = new Twitch();

        $streamInput = $inputProcessor->generateInputCmd();
        $streamOutput = $outputProcessor->generateOutputCmd();

        $streamCommand = sprintf('ffmpeg %s %s -metadata broadcast_id=%d', $streamInput, $streamOutput, $broadcast->getBroadcastId());
        echo $streamCommand;
    }

    public function stopBroadcast(LiveBroadcast $broadcast)
    {
        // @Todo find broadcast PID by broadcast_id metatag
    }
}