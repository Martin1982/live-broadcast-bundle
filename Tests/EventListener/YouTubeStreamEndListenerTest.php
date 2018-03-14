<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\StreamEndEvent;
use Martin1982\LiveBroadcastBundle\EventListener\YouTubeStreamEndListener;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use PHPUnit\Framework\TestCase;

/**
 * Class YouTubeStreamEndListenerTest
 */
class YouTubeStreamEndListenerTest extends TestCase
{
    /**
     * Test handling a stream end
     */
    public function testOnStreamEnd(): void
    {
        $api = $this->createMock(YouTubeApiService::class);
        $api->expects(static::any())
            ->method('transitionState')
            ->willReturn(true);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $event = $this->createMock(StreamEndEvent::class);
        $event->expects(static::any())
            ->method('getBroadcast')
            ->willReturn($broadcast);
        $event->expects(static::any())
            ->method('getChannel')
            ->willReturn($channel);

        $listener = new YouTubeStreamEndListener($api);
        $listener->onStreamEnd($event);
        $this->addToAssertionCount(1);
    }

    /**
     * Test events are registered
     */
    public function testGetSubscribedEvents(): void
    {
        $events = YouTubeStreamEndListener::getSubscribedEvents();
        self::assertArrayHasKey(StreamEndEvent::NAME, $events);
    }
}
