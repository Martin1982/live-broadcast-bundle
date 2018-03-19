<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\EventListener\FacebookPreBroadcastListener;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;
use PHPUnit\Framework\TestCase;

/**
 * Class FacebookPreBroadcastListenerTest
 */
class FacebookPreBroadcastListenerTest extends TestCase
{
    /**
     * @var FacebookApiService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $facebookService;

    /**
     * @var FacebookPreBroadcastListener
     */
    private $eventListener;

    /**
     *
     */
    public function setUp()
    {
        $this->facebookService = $this->createMock(FacebookApiService::class);
        $this->eventListener = new FacebookPreBroadcastListener($this->facebookService);
    }

    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     * @throws \ReflectionException
     */
    public function testOnPreBroadcast(): void
    {
        $broadcast = new LiveBroadcast();
        $output = new OutputFacebook($this->facebookService);

        $this->facebookService->expects(self::once())
            ->method('createFacebookLiveVideo')
            ->with($broadcast, $output)
            ->willReturn('facebook.stream.url');

        $event = new PreBroadcastEvent($broadcast, $output);

        $this->eventListener->onPreBroadcast($event);

        $reflection = new \ReflectionClass($output);
        $property = $reflection->getProperty('streamUrl');
        $property->setAccessible(true);

        self::assertEquals('facebook.stream.url', $property->getValue($output));
    }

    /**
     * Test subscribed events
     */
    public function testSubscribedEvents(): void
    {
        $events = FacebookPreBroadcastListener::getSubscribedEvents();
        self::assertCount(1, $events);
        self::assertArrayHasKey(PreBroadcastEvent::NAME, $events);
    }
}
