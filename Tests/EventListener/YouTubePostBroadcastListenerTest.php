<?php

namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastEvent;
use Martin1982\LiveBroadcastBundle\EventListener\YouTubePostBroadcastListener;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use PHPUnit\Framework\TestCase;

/**
 * Class YouTubePostBroadcastListenerTest
 */
class YouTubePostBroadcastListenerTest extends TestCase
{
    /**
     * @var YouTubeApiService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $api;

    /**
     * @var GoogleRedirectService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    public function testOnPostBroadcast()
    {
        $this->redirect->expects($this->any())
            ->method('getOAuthRedirectUrl')
            ->willReturn('http://some.url');

        $this->api->expects($this->any())
            ->method('initApiClients')
            ->willReturn(true);

        $this->api->expects($this->any())
            ->method('transitionState')
            ->willReturn(true);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $output = $this->createMock(OutputYouTube::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $output->expects($this->any())
            ->method('getChannel')
            ->willReturn($channel);

        $event = $this->createMock(PostBroadcastEvent::class);
        $event->expects($this->any())
            ->method('getLiveBroadCast')
            ->willReturn($broadcast);
        $event->expects($this->any())
            ->method('getOutput')
            ->willReturn($output);

        $listener = new YouTubePostBroadcastListener($this->api, $this->redirect);
        $listener->onPostBroadcast($event);
    }

    /**
     * Test that subscribed events are registered
     */
    public function testGetSubscribedEvents()
    {
        $events = YouTubePostBroadcastListener::getSubscribedEvents();
        self::assertArrayHasKey(PostBroadcastEvent::NAME, $events);
    }

    /**
     * Setup mock objects
     */
    protected function setUp()
    {
        $this->api = $this->createMock(YouTubeApiService::class);
        $this->redirect = $this->createMock(GoogleRedirectService::class);
    }
}
