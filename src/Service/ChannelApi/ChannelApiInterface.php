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
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastApiException;

/**
 * Interface ChannelApiInterface
 */
interface ChannelApiInterface
{
    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws LiveBroadcastApiException
     */
    public function createLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel);

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws LiveBroadcastApiException
     */
    public function updateLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel);

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws LiveBroadcastApiException
     */
    public function removeLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel);

    /**
     * @param PlannedChannelInterface $channel
     * @param int|string              $externalId
     *
     * @throws LiveBroadcastApiException
     */
    public function sendEndSignal(PlannedChannelInterface $channel, int|string $externalId);

    /**
     * Test if the API allows streaming
     *
     * @param AbstractChannel $channel
     *
     * @return bool
     *
     * @throws LiveBroadcastApiException
     */
    public function canStream(AbstractChannel $channel): bool;
}
