<?php

namespace Martin1982\LiveBroadcastBundle\Event;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PreBroadcastEvent
 * @package Martin1982\LiveBroadcastBundle\Event
 */
class PreBroadcastEvent extends AbstractBroadcastEvent
{
    const NAME = 'martin1982.livebroadcast.pre_broadcast';
}
