<?php declare(strict_types=1);

/**
 * Spinnin' Platform - All rights reserved.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\EventListener\StreamAnnouncementListener;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

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
     * @var MessageBusInterface|MockObject
     */
    protected $bus;

    /**
     * @var Envelope
     */
    protected $envelope;

    /**
     * Setup mock objects
     */
    public function setUp()
    {
        $this->manager = $this->createMock(BroadcastManager::class);
        $this->lifecycle = $this->createMock(LifecycleEventArgs::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->envelope = new Envelope(new \stdClass());
    }

    /**
     * Test preUpdate
     */
    public function testPreUpdate(): void
    {
        $broadcast = $this->getBroadcastMock();
        $this->lifecycle->expects(self::atLeastOnce())->method('getObject')->willReturn($broadcast);
        $this->bus->expects(self::atLeastOnce())->method('dispatch')->willReturn($this->envelope);

        $listener = new StreamAnnouncementListener($this->bus, $this->manager);
        $listener->preUpdate($this->lifecycle);
    }

    /**
     * Test prePersist
     */
    public function testPostPersist(): void
    {
        $broadcast = $this->getBroadcastMock();
        $this->lifecycle->expects(self::atLeastOnce())->method('getObject')->willReturn($broadcast);
        $this->bus->expects(self::atLeastOnce())->method('dispatch')->willReturn($this->envelope);

        $listener = new StreamAnnouncementListener($this->bus, $this->manager);
        $listener->postPersist($this->lifecycle);
    }

    /**
     * Test preRemove
     */
    public function testPreRemove(): void
    {
        $broadcast = $this->getBroadcastMock();
        $this->lifecycle->expects(self::atLeastOnce())->method('getObject')->willReturn($broadcast);
        $this->bus->expects(self::atLeastOnce())->method('dispatch')->willReturn($this->envelope);

        $listener = new StreamAnnouncementListener($this->bus, $this->manager);
        $listener->preRemove($this->lifecycle);
    }

    /**
     * Test preRemove with another type of object
     */
    public function testWithNonBroadcastObject(): void
    {
        $this->lifecycle->expects(self::atLeastOnce())->method('getObject')->willReturn(new \stdClass());
        $this->manager->expects(self::never())->method('preDelete');

        $listener = new StreamAnnouncementListener($this->bus, $this->manager);
        $listener->preRemove($this->lifecycle);
    }

    /**
     * FunctionDescription
     *
     * @return LiveBroadcast|MockObject
     */
    protected function getBroadcastMock()
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())->method('getOutputChannels')->willReturn([
            $this->getChannelMock(),
            $this->getChannelMock(),
        ]);
        $broadcast->expects(self::atLeastOnce())->method('getBroadcastId')->willReturn(10);

        return $broadcast;
    }

    /**
     * FunctionDescription
     *
     * @return AbstractChannel|MockObject
     */
    protected function getChannelMock()
    {
        $channelMock = $this->createMock(AbstractChannel::class);
        $channelMock->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(10);

        return $channelMock;
    }
}
