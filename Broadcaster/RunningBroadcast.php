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
     * @param int $channelId
     */
    public function __construct($broadcastId, $processId, $channelId)
    {
        $this->broadcastId = (int) $broadcastId;
        $this->processId = (int) $processId;
        $this->channelId = (int) $channelId;
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
        return ($this->broadcastId === $broadcast->getBroadcastId()) && ($this->channelId === $channel->getChannelId());
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ($this->getProcessId() !== 0) &&
            ($this->getBroadcastId() !== 0) &&
            ($this->getChannelId() !== 0);
    }
}
