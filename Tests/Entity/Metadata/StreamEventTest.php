<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Metadata;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamEventTest
 */
class StreamEventTest extends TestCase
{
    /**
     * @var StreamEvent
     */
    private $streamEvent;

    /**
     * Setup default test object
     *
     * @throws \ReflectionException
     */
    public function setUp()
    {
        $this->streamEvent = new StreamEvent();
        $this->streamEvent->setBroadcast(new LiveBroadcast());
        $this->streamEvent->setChannel(new ChannelYouTube());
        $this->streamEvent->setExternalStreamId('youtube.id');

        $reflection = new \ReflectionClass($this->streamEvent);
        $property = $reflection->getProperty('eventId');
        $property->setAccessible(true);
        $property->setValue($this->streamEvent, 1);
    }

    /**
     * Test retrieving the event id
     */
    public function testEventId(): void
    {
        self::assertEquals(1, $this->streamEvent->getEventId());
    }

    /**
     *
     */
    public function testBroadcast(): void
    {
        self::assertInstanceOf(LiveBroadcast::class, $this->streamEvent->getBroadcast());
    }

    /**
     *
     */
    public function testChannel(): void
    {
        self::assertInstanceOf(ChannelYouTube::class, $this->streamEvent->getChannel());
    }

    /**
     *
     */
    public function testYouTubeId(): void
    {
        self::assertEquals('youtube.id', $this->streamEvent->getExternalStreamId());
    }
}
