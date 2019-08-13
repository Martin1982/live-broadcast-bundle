<?php declare(strict_types=1);

/**
 * Spinnin' Platform - All rights reserved.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\EventListener\StreamAnnouncementListener;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamAnnouncementListenerTest
 */
class StreamAnnouncementListenerTest extends TestCase
{
    /**
     * @var BroadcastManager|MockObject
     */
    protected $manager;

    /**
     * @var LifecycleEventArgs|MockObject
     */
    protected $lifecycle;

    /**
     * Setup mock objects
     */
    public function setUp()
    {
        $this->manager = $this->createMock(BroadcastManager::class);
        $this->lifecycle = $this->createMock(LifecycleEventArgs::class);
    }

    /**
     * Test preUpdate
     */
    public function testPreUpdate(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $this->lifecycle->expects(self::atLeastOnce())->method('getObject')->willReturn($broadcast);
        $this->manager->expects(self::atLeastOnce())->method('preUpdate')->willReturn(true);

        $listener = new StreamAnnouncementListener($this->manager);
        $listener->preUpdate($this->lifecycle);
    }

    /**
     * Test prePersist
     */
    public function testPrePersist(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $this->lifecycle->expects(self::atLeastOnce())->method('getObject')->willReturn($broadcast);
        $this->manager->expects(self::atLeastOnce())->method('preInsert')->willReturn(true);

        $listener = new StreamAnnouncementListener($this->manager);
        $listener->prePersist($this->lifecycle);
    }

    /**
     * Test preRemove
     */
    public function testPreRemove(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $this->lifecycle->expects(self::atLeastOnce())->method('getObject')->willReturn($broadcast);
        $this->manager->expects(self::atLeastOnce())->method('preDelete')->willReturn(true);

        $listener = new StreamAnnouncementListener($this->manager);
        $listener->preRemove($this->lifecycle);
    }

    /**
     * Test preRemove with another type of object
     */
    public function testWithNonBroadcastObject(): void
    {
        $this->lifecycle->expects(self::atLeastOnce())->method('getObject')->willReturn(new \stdClass());
        $this->manager->expects(self::never())->method('preDelete');

        $listener = new StreamAnnouncementListener($this->manager);
        $listener->preRemove($this->lifecycle);
    }
}
