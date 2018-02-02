<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Metadata;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class YouTubeEventTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Metadata
 */
class YouTubeEventTest extends TestCase
{
    /**
     * @var YouTubeEvent
     */
    private $youTubeEvent;

    /**
     * Setup default test object
     */
    public function setUp()
    {
        $this->youTubeEvent = new YouTubeEvent();
        $this->youTubeEvent->setBroadcast(new LiveBroadcast());
        $this->youTubeEvent->setChannel(new ChannelYouTube());
        $this->youTubeEvent->setLastKnownState('testing');
        $this->youTubeEvent->setYouTubeId('youtube.id');

        $reflection = new \ReflectionClass($this->youTubeEvent);
        $property = $reflection->getProperty('eventId');
        $property->setAccessible(true);
        $property->setValue($this->youTubeEvent, 1);
    }

    /**
     * Test retrieving the event id
     */
    public function testEventId()
    {
        self::assertEquals(1, $this->youTubeEvent->getEventId());
    }

    /**
     *
     */
    public function testBroadcast()
    {
        self::assertInstanceOf(LiveBroadcast::class, $this->youTubeEvent->getBroadcast());
    }

    /**
     *
     */
    public function testChannel()
    {
        self::assertInstanceOf(ChannelYouTube::class, $this->youTubeEvent->getChannel());
    }

    /**
     *
     */
    public function testLastKnownState()
    {
        self::assertEquals(YouTubeEvent::STATE_REMOTE_TESTING, $this->youTubeEvent->getLastKnownState());
    }

    /**
     *
     */
    public function testYouTubeId()
    {
        self::assertEquals('youtube.id', $this->youTubeEvent->getYouTubeId());
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testLocalStateByRemoteStateException()
    {
        $this->youTubeEvent->getLocalStateByRemoteState('does-not-exist');
    }

    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testLocalStateByRemoteState()
    {
        self::assertEquals(
            YouTubeEvent::STATE_LOCAL_TESTING,
            $this->youTubeEvent->getLocalStateByRemoteState('testing')
        );
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testRemoteStateByLocalStateException()
    {
        $this->youTubeEvent->getRemoteStateByLocalState('??');
    }

    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testRemoteStateByLocalState()
    {
        self::assertEquals(
            YouTubeEvent::STATE_REMOTE_TESTING,
            $this->youTubeEvent->getRemoteStateByLocalState(YouTubeEvent::STATE_LOCAL_TESTING)
        );
    }
}
