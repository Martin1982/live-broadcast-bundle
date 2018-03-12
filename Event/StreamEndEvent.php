<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Event;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
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
    public const NAME = 'martin1982.livebroadcast.stream_end';

    /**
     * @var LiveBroadcast
     */
    protected $broadcast;

    /**
     * @var AbstractChannel
     */
    protected $channel;

    /**
     * StreamEndEvent constructor
     *
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     */
    public function __construct(LiveBroadcast $broadcast, AbstractChannel $channel)
    {
        $this->broadcast = $broadcast;
        $this->channel = $channel;
    }

    /**
     * @return LiveBroadcast
     */
    public function getBroadcast(): LiveBroadcast
    {
        return $this->broadcast;
    }

    /**
     * @return AbstractChannel
     */
    public function getChannel(): AbstractChannel
    {
        return $this->channel;
    }
}
