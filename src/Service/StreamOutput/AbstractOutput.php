<?php

declare(strict_types=1);

/**
 * live-broadcast-bundle - All rights reserved
 */

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;

/**
 * Class AbstractOutput
 */
abstract class AbstractOutput implements OutputInterface
{
    /**
     * @var string|null
     */
    protected ?string $lastError = null;

    /**
     * @var AbstractChannel|null
     */
    protected ?AbstractChannel $channel = null;

    /**
     * @param AbstractChannel $channel
     *
     * @return OutputInterface
     */
    public function setChannel(AbstractChannel $channel): OutputInterface
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get the last validation error for a stream channel
     *
     * @return string
     */
    public function getLastValidationError(): string
    {
        return $this->lastError;
    }
}
