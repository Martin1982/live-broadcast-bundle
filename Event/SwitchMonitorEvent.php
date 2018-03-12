<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Event;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SwitchMonitorEvent
 */
class SwitchMonitorEvent extends Event
{
    public const NAME = 'martin1982.livebroadcast.switch_monitor';

    /**
     * @var RunningBroadcast
     */
    protected $monitorBroadcast;

    /**
     * @var LiveBroadcast
     */
    protected $plannedBroadcast;

    /**
     * @var AbstractChannel
     */
    protected $channel;

    /**
     * SwitchMonitorEvent constructor.
     *
     * @param RunningBroadcast $monitor
     * @param LiveBroadcast    $planned
     * @param AbstractChannel  $channel
     */
    public function __construct(RunningBroadcast $monitor, LiveBroadcast $planned, AbstractChannel $channel)
    {
        $this->monitorBroadcast = $monitor;
        $this->plannedBroadcast = $planned;
        $this->channel = $channel;
    }

    /**
     * @return RunningBroadcast
     */
    public function getMonitorBroadcast(): RunningBroadcast
    {
        return $this->monitorBroadcast;
    }

    /**
     * @return LiveBroadcast
     */
    public function getPlannedBroadcast(): LiveBroadcast
    {
        return $this->plannedBroadcast;
    }

    /**
     * @return AbstractChannel
     */
    public function getChannel(): AbstractChannel
    {
        return $this->channel;
    }
}
