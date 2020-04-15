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
use Symfony\Component\Messenger\MessageBusInterface;

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
     * @var MessageBusInterface
     */
    protected $bus;

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
    public function prePersist(LifecycleEventArgs $args): void
    {
        $broadcast = $this->getBroadcast($args->getObject());

        if ($broadcast) {
            $action = StreamServiceAnnouncement::ACTION_PRE_PERSIST;
            $channelIds = $this->getChannelIds($broadcast->getOutputChannels());
            $serviceAnnouncement = new StreamServiceAnnouncement($action, $broadcast->getBroadcastId(), $channelIds);
            $this->bus->dispatch($serviceAnnouncement);
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
            $channelIds = $this->getChannelIds($broadcast->getOutputChannels());
            $serviceAnnouncement = new StreamServiceAnnouncement($action, $broadcast->getBroadcastId(), $channelIds);
            $this->bus->dispatch($serviceAnnouncement);
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
            $channelIds = $this->getChannelIds($broadcast->getOutputChannels());
            $serviceAnnouncement = new StreamServiceAnnouncement($action, $broadcast->getBroadcastId(), $channelIds);
            $this->bus->dispatch($serviceAnnouncement);
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
}
