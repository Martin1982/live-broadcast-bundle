<?php declare(strict_types=1);

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
    protected $lastError;

    /**
     * @var AbstractChannel|null
     */
    protected $channel;

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
     * Test if the channel config is still valid
     *
     * @return bool
     */
    public function isAllowedToStream(): bool
    {
        return true;
    }

    /**
     * Get the last validation error for a stream channel
     *
     * @return string
     */
    public function getLastValidationError(): string
    {
        $this->isAllowedToStream();

        return $this->lastError;
    }
}
