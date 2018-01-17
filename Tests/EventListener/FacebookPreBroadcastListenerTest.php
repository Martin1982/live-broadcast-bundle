<?php

namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\EventListener\FacebookPreBroadcastListener;
use Martin1982\LiveBroadcastBundle\Service\FacebookApiService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;
use PHPUnit\Framework\TestCase;

/**
 * Class FacebookPreBroadcastListenerTest
 * @package Martin1982\LiveBroadcastBundle\Tests\EventListener
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
     */
    public function testOnPreBroadcast()
    {
        $broadcast = new LiveBroadcast();
        $output = new OutputFacebook();

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

    public function testSubscribedEvents()
    {
        $events = $this->eventListener->getSubscribedEvents();
        self::assertCount(1, $events);
        self::assertArrayHasKey(PreBroadcastEvent::NAME, $events);
    }
}
