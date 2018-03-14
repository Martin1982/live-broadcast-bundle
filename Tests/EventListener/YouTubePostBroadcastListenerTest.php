<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastEvent;
use Martin1982\LiveBroadcastBundle\EventListener\YouTubePostBroadcastListener;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
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

    /**
     * Test the onPostBroadcast method
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testOnPostBroadcast(): void
    {
        $this->redirect->expects(static::any())
            ->method('getOAuthRedirectUrl')
            ->willReturn('http://some.url');

        $this->api->expects(static::any())
            ->method('initApiClients')
            ->willReturn(true);

        $this->api->expects(static::any())
            ->method('transitionState')
            ->willReturn(true);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $output = $this->createMock(OutputYouTube::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $output->expects(static::any())
            ->method('getChannel')
            ->willReturn($channel);

        $event = $this->createMock(PostBroadcastEvent::class);
        $event->expects(static::any())
            ->method('getLiveBroadCast')
            ->willReturn($broadcast);
        $event->expects(static::any())
            ->method('getOutput')
            ->willReturn($output);

        $listener = new YouTubePostBroadcastListener($this->api, $this->redirect);
        $listener->onPostBroadcast($event);
        $this->addToAssertionCount(1);
    }

    /**
     * Test that subscribed events are registered
     */
    public function testGetSubscribedEvents(): void
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
