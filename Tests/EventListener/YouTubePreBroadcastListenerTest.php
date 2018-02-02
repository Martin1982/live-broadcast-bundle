<?php

namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\EventListener\YouTubePreBroadcastListener;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use PHPUnit\Framework\TestCase;

/**
 * Class YouTubePreBroadcastListenerTest
 */
class YouTubePreBroadcastListenerTest extends TestCase
{

    /**
     * Test handling the prebroadcast event
     */
    public function testOnPreBroadcast()
    {
        $stream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

        $api = $this->createMock(YouTubeApiService::class);
        $api->expects($this->any())
            ->method('initApiClients')
            ->willReturn(true);
        $api->expects($this->any())
            ->method('getStream')
            ->willReturn($stream);
        $api->expects($this->any())
            ->method('getStreamUrl')
            ->willReturn('rtmp://stream.url');

        $redirect = $this->createMock(GoogleRedirectService::class);
        $redirect->expects($this->any())
            ->method('getOAuthRedirectUrl')
            ->willReturn('http://test.url');

        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $output = $this->createMock(OutputYouTube::class);
        $output->expects($this->any())
            ->method('setStreamUrl');
        $output->expects($this->any())
            ->method('getChannel')
            ->willReturn($channel);

        $event = $this->createMock(PreBroadcastEvent::class);
        $event->expects($this->any())
            ->method('getLiveBroadcast')
            ->willReturn($liveBroadcast);
        $event->expects($this->any())
            ->method('getOutput')
            ->willReturn($output);

        $listener = new YouTubePreBroadcastListener($api, $redirect);
        $listener->onPreBroadcast($event);
        $this->addToAssertionCount(1);
    }

    /**
     * Test subscribed events availability
     */
    public function testGetSubscribedEvents()
    {
        $events = YouTubePreBroadcastListener::getSubscribedEvents();
        self::assertArrayHasKey(PreBroadcastEvent::NAME, $events);
    }
}
