<?php declare(strict_types=1);

/**
 * live-broadcast-bundle - All rights reserved
 */
namespace Martin1982\LiveBroadcastBundle\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Message\StreamServiceAnnouncement;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Class StreamAnnouncementListener
 */
class StreamAnnouncementListener
{
    /**
     * @var BroadcastManager
     */
    protected BroadcastManager $broadcastManager;

    /**
     * @var MessageBusInterface
     */
    protected MessageBusInterface $bus;

    /**
     * StreamAnnouncementListener constructor.
     *
     * @param MessageBusInterface $bus
     * @param BroadcastManager    $broadcastManager
     */
    public function __construct(MessageBusInterface $bus, BroadcastManager $broadcastManager)
    {
        $this->bus = $bus;
        $this->broadcastManager = $broadcastManager;
    }

    /**
     * Take action before persisting
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $broadcast = $this->getBroadcast($args->getObject());

        if ($broadcast) {
            $action = StreamServiceAnnouncement::ACTION_PRE_PERSIST;
            $this->dispatchAnnouncement($broadcast, $action);
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
            $action = StreamServiceAnnouncement::ACTION_PRE_UPDATE;
            $this->dispatchAnnouncement($broadcast, $action);
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
            $action = StreamServiceAnnouncement::ACTION_PRE_REMOVE;
            $this->dispatchAnnouncement($broadcast, $action);
        }
    }

    /**
     * Get the live broadcast when available
     *
     * @param mixed $object
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

    /**
     * FunctionDescription
     *
     * @param AbstractChannel[]|ArrayCollection $channels
     *
     * @return array
     */
    protected function getChannelIds($channels): array
    {
        $ids = [];

        foreach ($channels as $channel) {
            $ids[] = $channel->getChannelId();
        }

        return $ids;
    }

    /**
     * Dispatch announcement to the queue
     *
     * @param LiveBroadcast $broadcast
     * @param int           $action
     *
     * @return void
     */
    private function dispatchAnnouncement(LiveBroadcast $broadcast, int $action): void
    {
        $channelIds = $this->getChannelIds($broadcast->getOutputChannels());
        $serviceAnnouncement = new StreamServiceAnnouncement($action, $broadcast->getBroadcastId(), $channelIds);
        $envelope = new Envelope($serviceAnnouncement, [
            new DelayStamp(5000),
        ]);
        $this->bus->dispatch($envelope);
    }
}
