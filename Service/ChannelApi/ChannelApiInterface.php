<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlannedChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Interface ChannelApiInterface
 */
interface ChannelApiInterface
{
    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     */
    public function createLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel);

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     */
    public function updateLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel);

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws LiveBroadcastOutputException
     */
    public function removeLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel);

    /**
     * @param PlannedChannelInterface $channel
     * @param string|int              $externalId
     */
    public function sendEndSignal(PlannedChannelInterface $channel, $externalId);

    /**
     * Test if the API allows streaming
     *
     * @param AbstractChannel $channel
     *
     * @return bool
     */
    public function canStream(AbstractChannel $channel): bool;

    /**
     * Set if the entity manager is allowed to flush
     *
     * @param bool $canFlush
     */
    public function setCanFlush(bool $canFlush): void;
}
