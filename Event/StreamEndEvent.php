<?php

namespace Martin1982\LiveBroadcastBundle\Event;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class StreamEndEvent
 */
class StreamEndEvent extends Event
{
    /**
     * @var string
     */
    const NAME = 'martin1982.livebroadcast.stream_end';

    /**
     * @var LiveBroadcast
     */
    protected $broadcast;

    /**
     * @var BaseChannel
     */
    protected $channel;

    /**
     * StreamEndEvent constructor
     * @param LiveBroadcast $broadcast
     * @param BaseChannel $channel
     */
    public function __construct(LiveBroadcast $broadcast, BaseChannel $channel)
    {
        $this->broadcast = $broadcast;
        $this->channel = $channel;
    }

    /**
     * @return LiveBroadcast
     */
    public function getBroadcast()
    {
        return $this->broadcast;
    }

    /**
     * @return BaseChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
