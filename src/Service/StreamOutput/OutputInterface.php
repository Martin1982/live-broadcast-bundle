<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;

/**
 * Interface OutputInterface
 */
interface OutputInterface
{
    /**
     * @param AbstractChannel $channel
     *
     * @return OutputInterface
     */
    public function setChannel(AbstractChannel $channel): OutputInterface;

    /**
     * Validate channel usage
     *
     * @return bool
     */
    public function validate(): bool;

    /**
     * Give the cmd string to start the stream.
     *
     * @return string
     */
    public function generateOutputCmd(): string;

    /**
     * Returns the channel type
     *
     * @return string
     */
    public function getChannelType(): string;

    /**
     * Get the last validation error for a stream channel
     *
     * @return string
     */
    public function getLastValidationError(): string;
}
