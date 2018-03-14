<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Event;

/**
 * Class PreBroadcastEvent
 */
class PreBroadcastEvent extends AbstractBroadcastEvent
{
    const NAME = 'martin1982.livebroadcast.pre_broadcast';
}
