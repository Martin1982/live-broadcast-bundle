<?php declare(strict_types=1);

/**
 * live-broadcast-bundle - All rights reserved
 */
namespace Martin1982\LiveBroadcastBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;

/**
 * Class StreamAnnouncementListener
 */
class StreamAnnouncementListener
{
    /**
     * @var BroadcastManager
     */
    protected $broadcastManager;

    /**
     * StreamAnnouncementListener constructor.
     *
     * @param BroadcastManager $broadcastManager
     */
    public function __construct(BroadcastManager $broadcastManager)
    {
        $this->broadcastManager = $broadcastManager;
    }

    /**
     * Take action before persisting
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $broadcast = $this->getBroadcast($args->getObject());

        if ($broadcast) {
            $this->broadcastManager->preInsert($broadcast);
        }
    }

    /**
     * Take actions before updating
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $broadcast = $this->getBroadcast($args->getObject());

        if ($broadcast) {
            $this->broadcastManager->preUpdate($broadcast);
        }
    }

    /**
     * Take actions before removing
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        $broadcast = $this->getBroadcast($args->getObject());

        if ($broadcast) {
            $this->broadcastManager->preDelete($broadcast);
        }
    }

    /**
     * Get the live broadcast when available
     *
     * @param \stdClass $object
     *
     * @return LiveBroadcast|null
     */
    protected function getBroadcast($object): ?LiveBroadcast
    {
        if (!$object instanceof LiveBroadcast) {
            return null;
        }

        return $object;
    }
}
