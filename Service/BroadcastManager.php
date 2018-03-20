<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlanableChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiStack;

/**
 * Class BroadcastManager
 */
class BroadcastManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var StreamManager
     */
    protected $streamManager;

    /**
     * @var ChannelApiStack
     */
    protected $apiStack;

    /**
     * BroadcastManager constructor
     *
     * @param EntityManager   $entityManager
     * @param StreamManager   $streamManager
     * @param ChannelApiStack $apiStack
     */
    public function __construct(EntityManager $entityManager, StreamManager $streamManager, ChannelApiStack $apiStack)
    {
        $this->entityManager = $entityManager;
        $this->streamManager = $streamManager;
        $this->apiStack      = $apiStack;
    }

    /**
     * Get a broadcast by it's id
     *
     * @param string $broadcastId
     *
     * @return LiveBroadcast|null|Object
     */
    public function getBroadcastByid($broadcastId)
    {
        $broadcastRepository = $this->entityManager->getRepository(LiveBroadcast::class);

        return $broadcastRepository->findOneBy([ 'broadcastId' => (int) $broadcastId ]);
    }

    /**
     * Handles API calls for new broadcasts
     *
     * @param LiveBroadcast $broadcast
     */
    public function preInsert(LiveBroadcast $broadcast): void
    {
        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof PlanableChannelInterface) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api) {
                    $api->createLiveEvent($broadcast, $channel);
                }
            }
        }
    }

    /**
     * Handles API calls for existing broadcasts
     *
     * @param LiveBroadcast $broadcast
     */
    public function preUpdate(LiveBroadcast $broadcast): void
    {
        $previousState = $this->getBroadcastByid($broadcast->getBroadcastId());

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
        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof PlanableChannelInterface) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api) {
                    $api->removeLiveEvent($broadcast, $channel);
                }
            }
        }
    }

    /**
     * End a broadcast on all channels
     *
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channelToEnd
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function handleBroadcastEnd(LiveBroadcast $broadcast, AbstractChannel $channelToEnd = null): void
    {
        $channels = $broadcast->getOutputChannels();

        if ($channelToEnd) {
            $this->streamManager->endStream($broadcast, $channelToEnd);

            return;
        }

        foreach ($channels as $channel) {
            $this->streamManager->endStream($broadcast, $channel);
        }
    }

    /**
     * @param ArrayCollection $previousState
     * @param ArrayCollection $newState
     *
     * @return array
     */
    private function getAddedChannels($previousState, ArrayCollection $newState): array
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
     * @param ArrayCollection $previousState
     * @param ArrayCollection $newState
     *
     * @return array
     */
    private function getUnchangedChannels(ArrayCollection $previousState, ArrayCollection $newState): array
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
     * @param ArrayCollection $previousState
     * @param ArrayCollection $newState
     *
     * @return array
     */
    private function getDeletedChannels(ArrayCollection $previousState, ArrayCollection $newState): array
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
     */
    private function createLiveEvents(LiveBroadcast $broadcast, array $channels): void
    {
        foreach ($channels as $channel) {
            if ($channel instanceof PlanableChannelInterface) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api) {
                    $api->createLiveEvent($broadcast, $channel);
                }
            }
        }
    }

    /**
     * @param LiveBroadcast $broadcast
     * @param array         $channels
     */
    private function updateLiveEvents(LiveBroadcast $broadcast, array $channels): void
    {
        foreach ($channels as $channel) {
            if ($channel instanceof PlanableChannelInterface) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api) {
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
            if ($channel instanceof PlanableChannelInterface) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api) {
                    $api->removeLiveEvent($broadcast, $channel);
                }
            }
        }
    }
}
