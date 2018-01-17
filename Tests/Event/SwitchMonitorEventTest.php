<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Event;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\SwitchMonitorEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class SwitchMonitorEventTest
 */
class SwitchMonitorEventTest extends TestCase
{
    /**
     * Test getting the class properties
     */
    public function testGetProperties()
    {
        $running = $this->createMock(RunningBroadcast::class);
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(BaseChannel::class);

        $event = new SwitchMonitorEvent($running, $broadcast, $channel);

        self::assertEquals($running, $event->getMonitorBroadcast());
        self::assertEquals($broadcast, $event->getPlannedBroadcast());
        self::assertEquals($channel, $event->getChannel());
    }
}
