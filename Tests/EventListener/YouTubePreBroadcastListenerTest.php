<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\EventListener\YouTubePreBroadcastListener;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use PHPUnit\Framework\TestCase;

/**
 * Class YouTubePreBroadcastListenerTest
 */
class YouTubePreBroadcastListenerTest extends TestCase
{

    /**
     * Test handling the prebroadcast event
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testOnPreBroadcast(): void
    {
        $stream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

        $api = $this->createMock(YouTubeApiService::class);
        $api->expects(static::any())
            ->method('initApiClients')
            ->willReturn(true);
        $api->expects(static::any())
            ->method('getStream')
            ->willReturn($stream);
        $api->expects(static::any())
            ->method('getStreamUrl')
            ->willReturn('rtmp://stream.url');

        $redirect = $this->createMock(GoogleRedirectService::class);
        $redirect->expects(static::any())
            ->method('getOAuthRedirectUrl')
            ->willReturn('http://test.url');

        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $output = $this->createMock(OutputYouTube::class);
        $output->expects(static::any())
            ->method('setStreamUrl');
        $output->expects(static::any())
            ->method('getChannel')
            ->willReturn($channel);

        $event = $this->createMock(PreBroadcastEvent::class);
        $event->expects(static::any())
            ->method('getLiveBroadcast')
            ->willReturn($liveBroadcast);
        $event->expects(static::any())
            ->method('getOutput')
            ->willReturn($output);

        $listener = new YouTubePreBroadcastListener($api, $redirect);
        $listener->onPreBroadcast($event);
        $this->addToAssertionCount(1);
    }

    /**
     * Test subscribed events availability
     */
    public function testGetSubscribedEvents(): void
    {
        $events = YouTubePreBroadcastListener::getSubscribedEvents();
        self::assertArrayHasKey(PreBroadcastEvent::NAME, $events);
    }
}
