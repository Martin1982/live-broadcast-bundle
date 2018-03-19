<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Metadata;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class YouTubeEventTest
 */
class YouTubeEventTest extends TestCase
{
    /**
     * @var YouTubeEvent
     */
    private $youTubeEvent;

    /**
     * Setup default test object
     *
     * @throws \ReflectionException
     */
    public function setUp()
    {
        $this->youTubeEvent = new YouTubeEvent();
        $this->youTubeEvent->setBroadcast(new LiveBroadcast());
        $this->youTubeEvent->setChannel(new ChannelYouTube());
        $this->youTubeEvent->setYouTubeId('youtube.id');

        $reflection = new \ReflectionClass($this->youTubeEvent);
        $property = $reflection->getProperty('eventId');
        $property->setAccessible(true);
        $property->setValue($this->youTubeEvent, 1);
    }

    /**
     * Test retrieving the event id
     */
    public function testEventId(): void
    {
        self::assertEquals(1, $this->youTubeEvent->getEventId());
    }

    /**
     *
     */
    public function testBroadcast(): void
    {
        self::assertInstanceOf(LiveBroadcast::class, $this->youTubeEvent->getBroadcast());
    }

    /**
     *
     */
    public function testChannel(): void
    {
        self::assertInstanceOf(ChannelYouTube::class, $this->youTubeEvent->getChannel());
    }

    /**
     *
     */
    public function testYouTubeId(): void
    {
        self::assertEquals('youtube.id', $this->youTubeEvent->getYouTubeId());
    }
}
