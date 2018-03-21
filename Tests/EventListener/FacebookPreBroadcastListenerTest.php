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
     * Test subscribed events
     */
    public function testSubscribedEvents(): void
    {
        $events = FacebookPreBroadcastListener::getSubscribedEvents();
        self::assertCount(1, $events);
        self::assertArrayHasKey(PreBroadcastEvent::NAME, $events);
    }
}
