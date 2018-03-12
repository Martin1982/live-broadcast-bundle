<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Event;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
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
    public function testGetProperties(): void
    {
        $running = $this->createMock(RunningBroadcast::class);
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(AbstractChannel::class);

        $event = new SwitchMonitorEvent($running, $broadcast, $channel);

        self::assertEquals($running, $event->getMonitorBroadcast());
        self::assertEquals($broadcast, $event->getPlannedBroadcast());
        self::assertEquals($channel, $event->getChannel());
    }
}
