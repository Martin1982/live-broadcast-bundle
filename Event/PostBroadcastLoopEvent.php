<?php

namespace Martin1982\LiveBroadcastBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class PostBroadcastLoopEvent
 * @package Martin1982\LiveBroadcastBundle\Event
 */
class PostBroadcastLoopEvent extends Event
{
    const NAME = 'martin1982.livebroadcast.post_broadcastloop';
}
