<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlanableChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
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
     * @var ChannelApiStack
     */
    protected $apiStack;

    /**
     * BroadcastManager constructor
     *
     * @param EntityManager   $entityManager
     * @param ChannelApiStack $apiStack
     */
    public function __construct(EntityManager $entityManager, ChannelApiStack $apiStack)
    {
        $this->entityManager = $entityManager;
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
        $broadcastRepository = $this->getBroadcastsRepository();

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
     * Send a end signal for a broadcast's stream
     *
     * @param StreamEvent $event
     *
     * @throws LiveBroadcastException
     */
    public function sendEndSignal(StreamEvent $event): void
    {
        $channel = $event->getChannel();

        if ($channel && $channel instanceof PlanableChannelInterface) {
            $api = $this->apiStack->getApiForChannel($channel);

            if ($api) {
                $event->setEndSignalSent(true);

                try {
                    $this->entityManager->persist($event);
                    $this->entityManager->flush();

                    $api->sendEndSignal($channel, $event->getExternalStreamId());
                } catch (OptimisticLockException $exception) {
                    throw new LiveBroadcastException(sprintf('Couldn\'t save broadcast end: %s', $exception->getMessage()));
                } catch (ORMInvalidArgumentException $exception) {
                    throw new LiveBroadcastException(sprintf('Couldn\'t save broadcast end: %s', $exception->getMessage()));
                } catch (ORMException $exception) {
                    throw new LiveBroadcastException(sprintf('Couldn\'t save broadcast end: %s', $exception->getMessage()));
                }
            }
        }
    }

    /**
     * @return LiveBroadcast[]|Collection
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function getPlannedBroadcasts()
    {
        $broadcasts = $this->getBroadcastsRepository()->getPlannedBroadcasts();

        if (!$broadcasts) {
            $broadcasts = [];
        }

        return $broadcasts;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository|\Martin1982\LiveBroadcastBundle\Entity\LiveBroadcastRepository
     */
    public function getBroadcastsRepository()
    {
        return $this->entityManager->getRepository(LiveBroadcast::class);
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository|\Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEventRepository
     */
    public function getEventsRepository()
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
