<?php

namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\StreamEndEvent;
use Martin1982\LiveBroadcastBundle\EventListener\YouTubeStreamEndListener;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;

/**
 * Class YouTubeStreamEndListenerTest
 */
class YouTubeStreamEndListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test handling a stream end
     */
    public function testOnStreamEnd()
    {
        $api = $this->createMock(YouTubeApiService::class);
        $api->expects($this->any())
            ->method('transitionState')
            ->willReturn(true);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $event = $this->createMock(StreamEndEvent::class);
        $event->expects($this->any())
            ->method('getBroadcast')
            ->willReturn($broadcast);
        $event->expects($this->any())
            ->method('getChannel')
            ->willReturn($channel);

        $listener = new YouTubeStreamEndListener($api);
        $listener->onStreamEnd($event);
    }

    /**
     * Test events are registered
     */
    public function testGetSubscribedEvents()
    {
        $events = YouTubeStreamEndListener::getSubscribedEvents();
        self::assertArrayHasKey(StreamEndEvent::NAME, $events);
    }
}
