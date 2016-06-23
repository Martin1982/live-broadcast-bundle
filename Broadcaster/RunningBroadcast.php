<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class RunningBroadcast.
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
     * RunningBroadcast constructor.
     *
     * @param int $broadcastId
     * @param int $processId
     */
    public function __construct($broadcastId, $processId, $channelId)
    {
        $this->broadcastId = $broadcastId;
        $this->processId = $processId;
        $this->channelId = $channelId;
    }

    /**
     * @return int
     */
    public function getBroadcastId()
    {
        return $this->broadcastId;
    }

    /**
     * @return int
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * @return int
     */
    public function getChannelId()
    {
        return $this->channelId;
    }

    /**
     * @param $broadcast
     * @param $channel
     * @return bool
     */
    public function isBroadcasting(LiveBroadcast $broadcast, BaseChannel $channel)
    {
        return $this->broadcastId === $broadcast->getBroadcastId() && $this->channelId === $channel->getChannelId();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ($this->getProcessId() !== null) &&
            ($this->getBroadcastId() !== null) &&
            ($this->getChannelId() !== null);
    }
}
