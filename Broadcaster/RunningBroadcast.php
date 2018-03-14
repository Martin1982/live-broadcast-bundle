<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class RunningBroadcast
 */
class RunningBroadcast
{
    /**
     * @var int
     */
    private $broadcastId;

    /**
     * @var int
     */
    private $processId;

    /**
     * @var int
     */
    private $channelId;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var bool|null
     */
    private $isMonitor;

    /**
     * RunningBroadcast constructor.
     *
     * @param int    $broadcastId
     * @param int    $processId
     * @param int    $channelId
     * @param string $environment
     * @param bool   $isMonitor
     */
    public function __construct($broadcastId, $processId, $channelId, $environment, $isMonitor = false)
    {
        $this->broadcastId = (int) $broadcastId;
        $this->processId = (int) $processId;
        $this->channelId = (int) $channelId;
        $this->environment = (string) $environment;
        $this->isMonitor = $isMonitor;
    }

    /**
     * @return int
     */
    public function getBroadcastId(): int
    {
        return $this->broadcastId;
    }

    /**
     * @return int
     */
    public function getProcessId(): int
    {
        return $this->processId;
    }

    /**
     * @return int
     */
    public function getChannelId(): int
    {
        return $this->channelId;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @return bool
     */
    public function isMonitor(): bool
    {
        return (bool) $this->isMonitor;
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @return bool
     */
    public function isBroadcasting(LiveBroadcast $broadcast, AbstractChannel $channel): bool
    {
        return ($this->broadcastId === $broadcast->getBroadcastId()) && ($this->channelId === $channel->getChannelId());
    }

    /**
     * @param string $kernelEnvironment
     *
     * @return bool
     */
    public function isValid($kernelEnvironment): bool
    {
        return ($kernelEnvironment === $this->getEnvironment()) &&
            ($this->getProcessId() !== 0) &&
            ($this->getBroadcastId() !== 0) &&
            ($this->getChannelId() !== 0);
    }
}
