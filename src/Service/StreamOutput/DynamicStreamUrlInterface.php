<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Interface DynamicStreamUrlInterface
 */
interface DynamicStreamUrlInterface extends OutputInterface
{
    /**
     * @return string
     */
    public function getStreamUrl(): string;

    /**
     * @param LiveBroadcast $broadcast
     */
    public function setBroadcast(LiveBroadcast $broadcast): void;
}
