<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Event;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
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
    public function testGetBroadcast()
    {
        self::assertInstanceOf(LiveBroadcast::class, $this->event->getBroadcast());
    }

    /**
     * Test retrieving the channel
     */
    public function testGetChannel()
    {
        self::assertInstanceOf(BaseChannel::class, $this->event->getChannel());
    }

    /**
     * Setup a basic test object
     */
    protected function setUp()
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(BaseChannel::class);

        $this->event = new StreamEndEvent($broadcast, $channel);
    }
}
