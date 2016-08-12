<?php

namespace Martin1982\LiveBroadcastBundle\Event;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SwitchMonitorEvent
 * @package Martin1982\LiveBroadcastBundle\Event
 */
class SwitchMonitorEvent extends Event
{
    const NAME = 'martin1982.livebroadcast.switch_monitor';

    /**
     * @var RunningBroadcast
     */
    protected $monitorBroadcast;

    /**
     * @var LiveBroadcast
     */
    protected $plannedBroadcast;

    /**
     * @var BaseChannel
     */
    protected $channel;

    /**
     * SwitchMonitorEvent constructor.
     * @param RunningBroadcast $monitorBroadcast
     * @param LiveBroadcast $plannedBroadcast
     * @param BaseChannel $channel
     */
    public function __construct(
        RunningBroadcast $monitorBroadcast,
        LiveBroadcast $plannedBroadcast,
        BaseChannel $channel
    ) {
        $this->monitorBroadcast = $monitorBroadcast;
        $this->plannedBroadcast = $plannedBroadcast;
        $this->channel = $channel;
    }

    /**
     * @return RunningBroadcast
     */
    public function getMonitorBroadcast()
    {
        return $this->monitorBroadcast;
    }

    /**
     * @return LiveBroadcast
     */
    public function getPlannedBroadcast()
    {
        return $this->plannedBroadcast;
    }

    /**
     * @return BaseChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
