<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

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
        
    }

    public function startBroadcast(LiveBroadcast $broadcast)
    {

    }

    public function stopBroadcast(LiveBroadcast $broadcast)
    {

    }
}