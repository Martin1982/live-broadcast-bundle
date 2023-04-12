<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlannedChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcastRepository;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEventRepository;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastApiException;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiInterface;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiStack;
use Psr\Log\LoggerInterface;

/**
 * Class BroadcastManager
 */
class BroadcastManager
{
    /**
     * BroadcastManager constructor
     *
     * @param EntityManager   $entityManager
     * @param ChannelApiStack $apiStack
     * @param LoggerInterface $logger
     */
    public function __construct(protected EntityManager $entityManager, protected ChannelApiStack $apiStack, private LoggerInterface $logger)
    {
    }

    /**
     * Get a broadcast by its id
     *
     * @param int|string $broadcastId
     *
     * @return null|Object<LiveBroadcast>
     */
    public function getBroadcastById(int|string $broadcastId): object|null
    {
        return $this->getBroadcastsRepository()
            ->findOneBy([ 'broadcastId' => (int) $broadcastId ]);
    }

    /**
     * Retrieve a channel by its id
     *
     * @param int $id
     *
     * @return AbstractChannel|null
     */
    public function getChannelById(int $id): ?AbstractChannel
    {
        return $this->entityManager
            ->getRepository(AbstractChannel::class)
            ->find($id);
    }

    /**
     * Handles API calls for new broadcasts
     *
     * @param LiveBroadcast $broadcast
     *
     * @throws LiveBroadcastApiException
     */
    public function preInsert(LiveBroadcast $broadcast): void
    {
        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof PlannedChannelInterface) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api instanceof ChannelApiInterface) {
                    $api->createLiveEvent($broadcast, $channel);
                }
            }
        }
    }

    /**
     * Handles API calls for existing broadcasts
     *
     * @param LiveBroadcast $broadcast
     *
     * @throws LiveBroadcastApiException
     */
    public function preUpdate(LiveBroadcast $broadcast): void
    {
        $previousState = $this->getBroadcastById($broadcast->getBroadcastId());

        if (!$previousState) {
            return;
        }

        $oldChannelList = $previousState->getOutputChannels();
        $newChannelList = $broadcast->getOutputChannels();

        $newChannels = $this->getAddedChannels($oldChannelList, $newChannelList);
        $this->createLiveEvents($broadcast, $newChannels);

        $updatedChannels = $this->getUnchangedChannels($oldChannelList, $newChannelList);
        $this->updateLiveEvents($broadcast, $updatedChannels);

        $deletedChannels = $this->getDeletedChannels($oldChannelList, $newChannelList);
        $this->removeLiveEvents($broadcast, $deletedChannels);
    }

    /**
     * Handles API calls for broadcasts being deleted
     *
     * @param LiveBroadcast $broadcast
     */
    public function preDelete(LiveBroadcast $broadcast): void
    {
        $outputChannels = $broadcast->getOutputChannels();

        if ($outputChannels->count() > 0) {
            $this->removeLiveEvents($broadcast, $outputChannels->toArray());
        }
    }

    /**
     * Send an end signal for a broadcast's stream
     *
     * @param StreamEvent $event
     *
     * @throws LiveBroadcastApiException
     * @throws LiveBroadcastException
     */
    public function sendEndSignal(StreamEvent $event): void
    {
        $channel = $event->getChannel();

        if ($channel instanceof PlannedChannelInterface) {
            $api = $this->apiStack->getApiForChannel($channel);

            if ($api) {
                $event->setEndSignalSent(true);

                try {
                    $this->entityManager->persist($event);
                    $this->entityManager->flush();

                    $api->sendEndSignal($channel, $event->getExternalStreamId());
                } catch (ORMException $exception) {
                    throw new LiveBroadcastException(sprintf('Couldn\'t save broadcast end: %s', $exception->getMessage()));
                }
            }
        }
    }

    /**
     * @return LiveBroadcast[]|Collection
     *
     * @throws LiveBroadcastException
     */
    public function getPlannedBroadcasts(): array|Collection
    {
        $broadcasts = $this->getBroadcastsRepository()->getPlannedBroadcasts();

        if (!$broadcasts) {
            $broadcasts = [];
        }

        return $broadcasts;
    }

    /**
     * @return LiveBroadcastRepository
     */
    public function getBroadcastsRepository(): LiveBroadcastRepository
    {
        return $this->entityManager->getRepository(LiveBroadcast::class);
    }

    /**
     * @return StreamEventRepository
     */
    public function getEventsRepository(): StreamEventRepository
    {
        return $this->entityManager->getRepository(StreamEvent::class);
    }

    /**
     * @param Collection $previousState
     * @param Collection $newState
     *
     * @return array
     */
    private function getAddedChannels(Collection $previousState, Collection $newState): array
    {
        $channels = [];

        foreach ($newState as $channel) {
            if (!$previousState->contains($channel)) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }

    /**
     * @param Collection $previousState
     * @param Collection $newState
     *
     * @return array
     */
    private function getUnchangedChannels(Collection $previousState, Collection $newState): array
    {
        $channels = [];

        foreach ($newState as $channel) {
            if ($previousState->contains($channel)) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }

    /**
     * @param Collection $previousState
     * @param Collection $newState
     *
     * @return array
     */
    private function getDeletedChannels(Collection $previousState, Collection $newState): array
    {
        $channels = [];

        foreach ($previousState as $channel) {
            if (!$newState->contains($channel)) {
                $channels[] = $channel;
            }
        }

        return $channels;
    }

    /**
     * @param LiveBroadcast $broadcast
     * @param array         $channels
     *
     * @throws LiveBroadcastApiException
     */
    private function createLiveEvents(LiveBroadcast $broadcast, array $channels): void
    {
        foreach ($channels as $channel) {
            if ($channel instanceof PlannedChannelInterface && $channel instanceof AbstractChannel) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api instanceof ChannelApiInterface) {
                    $api->createLiveEvent($broadcast, $channel);
                }
            }
        }
    }

    /**
     * @param LiveBroadcast $broadcast
     * @param array         $channels
     *
     * @throws LiveBroadcastApiException
     */
    private function updateLiveEvents(LiveBroadcast $broadcast, array $channels): void
    {
        foreach ($channels as $channel) {
            if ($channel instanceof PlannedChannelInterface && $channel instanceof AbstractChannel) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api instanceof ChannelApiInterface) {
                    $api->updateLiveEvent($broadcast, $channel);
                }
            }
        }
    }

    /**
     * @param LiveBroadcast $broadcast
     * @param array         $channels
     */
    private function removeLiveEvents(LiveBroadcast $broadcast, array $channels): void
    {
        foreach ($channels as $channel) {
            if ($channel instanceof PlannedChannelInterface && $channel instanceof AbstractChannel) {
                $api = $this->apiStack->getApiForChannel($channel);
                $this->attemptDeleteOnApi($broadcast, $channel, $api);
            }
        }
    }

    /**
     * @param LiveBroadcast            $broadcast
     * @param AbstractChannel          $channel
     * @param ChannelApiInterface|null $api
     *
     */
    private function attemptDeleteOnApi(LiveBroadcast $broadcast, AbstractChannel $channel, ChannelApiInterface $api = null): void
    {
        if (!$api || !$channel instanceof PlannedChannelInterface) {
            return;
        }

        try {
            $api->removeLiveEvent($broadcast, $channel);
        } catch (LiveBroadcastApiException $exception) {
            $this->logger->error(sprintf('Couldn\'t remove broadcast from channel %s: %s', $channel->getChannelName(), $exception->getMessage()));
        }
    }
}
