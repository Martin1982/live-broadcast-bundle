<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class PostBroadcastLoopEvent
 */
class PostBroadcastLoopEvent extends Event
{
    public const NAME = 'martin1982.livebroadcast.post_broadcastloop';
}
