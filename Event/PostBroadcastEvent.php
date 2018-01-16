<?php

namespace Martin1982\LiveBroadcastBundle\Event;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PostBroadcastEvent
 * @package Martin1982\LiveBroadcastBundle\Event
 */
class PostBroadcastEvent extends AbstractBroadcastEvent
{
    const NAME = 'martin1982.livebroadcast.post_broadcast';
}
