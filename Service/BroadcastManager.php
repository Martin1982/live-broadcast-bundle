<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

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
     * BroadcastManager constructor
     *
     * @param EntityManager $entityManager
     * @param StreamManager $streamManager
     */
    public function __construct(EntityManager $entityManager, StreamManager $streamManager)
    {
        $this->entityManager = $entityManager;
        $this->streamManager = $streamManager;
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
}
