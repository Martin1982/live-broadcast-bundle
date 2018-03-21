<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Event;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\StreamEndEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamEndEventTest
 */
class StreamEndEventTest extends TestCase
{
    /**
     * @var StreamEndEvent
     */
    protected $event;

    /**
     * Test retrieving the broadcast
     */
    public function testGetBroadcast(): void
    {
        self::assertNotNull($this->event->getBroadcast());
    }

    /**
     * Test retrieving the channel
     */
    public function testGetChannel(): void
    {
        self::assertNotNull($this->event->getChannel());
    }

    /**
     * Setup a basic test object
     */
    protected function setUp()
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(AbstractChannel::class);

        $this->event = new StreamEndEvent($broadcast, $channel);
    }
}
