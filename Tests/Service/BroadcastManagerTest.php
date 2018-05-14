<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcastRepository;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiInterface;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiStack;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use PHPUnit\Framework\TestCase;

/**
 * Class BroadcastManagerTest
 */
class BroadcastManagerTest extends TestCase
{
    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var ChannelApiStack|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stack;

    /**
     * Test connection keepalive method
     */
    public function testKeepConnectionAlive(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::atLeastOnce())
            ->method('ping')
            ->willReturn(false);
        $connection->expects(self::atLeastOnce())
            ->method('close')
            ->willReturn(true);
        $connection->expects(self::atLeastOnce())
            ->method('connect')
            ->willReturn(true);

        /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects(self::atLeastOnce())
            ->method('getConnection')
            ->willReturn($connection);

        $manager = new BroadcastManager($entityManager, $this->stack);
        $manager->keepConnectionAlive();
    }

    /**
     * Test getting a broadcast entity by id
     */
    public function testGetBroadcastByid(): void
    {
        $broadcastEntity = $this->createMock(LiveBroadcast::class);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(static::any())
            ->method('findOneBy')
            ->with([ 'broadcastId' => 10 ])
            ->willReturn($broadcastEntity);

        $this->entityManager->expects(static::any())
            ->method('getRepository')
            ->willReturn($broadcastRepository);

        $broadcast = new BroadcastManager($this->entityManager, $this->stack);
        $result = $broadcast->getBroadcastByid('10');

        self::assertInstanceOf(LiveBroadcast::class, $result);
    }

    /**
     * Test that preinserts get executed
     */
    public function testPreInsert(): void
    {
        $channels = [ $this->createMock(ChannelFacebook::class) ];

        $api = $this->createMock(ChannelApiInterface::class);
        $api->expects(self::once())
            ->method('createLiveEvent')
            ->willReturn(true);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getOutputChannels')
            ->willReturn($channels);

        $this->stack->expects(self::atLeastOnce())
            ->method('getApiForChannel')
            ->willReturn($api);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcastManager->preInsert($broadcast);
    }

    /**
     * Test preupdate events
     */
    public function testPreUpdate(): void
    {
        $fbSpinninDeep = new ChannelFacebook();
        $fbSpinninDeep->setFbEntityId('fbspinnindeep');

        $fbSpinninRecords = new ChannelFacebook();
        $fbSpinninRecords->setFbEntityId('fbspinninrecords');

        $ytSpinninRecords = new ChannelYouTube();
        $ytSpinninRecords->setYouTubeChannelName('Spinnin\' Records');

        $ytSpinninDeep = new ChannelYouTube();
        $ytSpinninDeep->setYouTubeChannelName('Spinnin\' Deep');

        $broadcastNewState = $this->createMock(LiveBroadcast::class);
        $broadcastOldState = $this->createMock(LiveBroadcast::class);
        $broadcastRepository = $this->createMock(EntityRepository::class);
        $api = $this->createMock(FacebookApiService::class);

        $oldChannelList = new ArrayCollection();
        $oldChannelList->add($fbSpinninDeep);
        $oldChannelList->add($ytSpinninRecords);
        $oldChannelList->add($ytSpinninDeep);

        $newChannelList = new ArrayCollection();
        $newChannelList->add($fbSpinninRecords);
        $newChannelList->add($ytSpinninRecords);

        $api->expects(self::atLeastOnce())
            ->method('createLiveEvent')
            ->willReturn(true);

        $api->expects(self::atLeastOnce())
            ->method('updateLiveEvent')
            ->willReturn(true);

        $api->expects(self::atLeastOnce())
            ->method('removeLiveEvent')
            ->willReturn(true);

        $broadcastOldState->expects(self::atLeastOnce())
            ->method('getOutputChannels')
            ->willReturn($oldChannelList);

        $broadcastNewState->expects(self::atLeastOnce())
            ->method('getOutputChannels')
            ->willReturn($newChannelList);

        $broadcastRepository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn($broadcastOldState);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->with(LiveBroadcast::class)
            ->willReturn($broadcastRepository);

        $this->stack->expects(self::atLeast(3))
            ->method('getApiForChannel')
            ->willReturn($api);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcastManager->preUpdate($broadcastNewState);
    }

    /**
     * Test that no updates are done when there is no previous state
     */
    public function testUpdateWithNoPreviousState():void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);

        $broadcastRepository = $this->createMock(EntityRepository::class);
        $broadcastRepository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->with(LiveBroadcast::class)
            ->willReturn($broadcastRepository);

        $this->stack->expects(self::never())
            ->method('getApiForChannel');

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcastManager->preUpdate($broadcast);
    }

    /**
     * Test pre delete actions
     */
    public function testPreDelete(): void
    {
        $channels = [ $this->createMock(ChannelFacebook::class) ];

        $api = $this->createMock(ChannelApiInterface::class);
        $api->expects(self::once())
            ->method('removeLiveEvent')
            ->willReturn(true);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getOutputChannels')
            ->willReturn($channels);

        $this->stack->expects(self::atLeastOnce())
            ->method('getApiForChannel')
            ->willReturn($api);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcastManager->preDelete($broadcast);
    }

    /**
     * Test sending an end signal
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testSendEndSignal(): void
    {
        $channel = $this->createMock(ChannelFacebook::class);

        $api = $this->createMock(ChannelApiInterface::class);
        $api->expects(self::atLeastOnce())
            ->method('sendEndSignal')
            ->willReturn(true);

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);

        $this->stack->expects(self::atLeastOnce())
            ->method('getApiForChannel')
            ->willReturn($api);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('persist')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcastManager->sendEndSignal($streamEvent);
    }

    /**
     * Test getting an events repository
     */
    public function testGetEventsRepository(): void
    {
        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->with(StreamEvent::class)
            ->willReturn($this->createMock(EntityRepository::class));

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        self::assertInstanceOf(EntityRepository::class, $broadcastManager->getEventsRepository());
    }

    /**
     * Test sending an end signal
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testSendEndSignalLockException(): void
    {

        $channel = $this->createMock(ChannelFacebook::class);

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);

        $api = $this->createMock(ChannelApiInterface::class);
        $api->expects(self::atLeastOnce())
            ->method('sendEndSignal')
            ->willThrowException(new OptimisticLockException('some error', $streamEvent));

        $this->stack->expects(self::atLeastOnce())
            ->method('getApiForChannel')
            ->willReturn($api);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('persist')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcastManager->sendEndSignal($streamEvent);
    }

    /**
     * Test sending an end signal
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testSendEndSignalArgumentException(): void
    {

        $channel = $this->createMock(ChannelFacebook::class);

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);

        $api = $this->createMock(ChannelApiInterface::class);
        $api->expects(self::atLeastOnce())
            ->method('sendEndSignal')
            ->willThrowException(new ORMInvalidArgumentException('some error'));

        $this->stack->expects(self::atLeastOnce())
            ->method('getApiForChannel')
            ->willReturn($api);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('persist')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcastManager->sendEndSignal($streamEvent);
    }

    /**
     * Test sending an end signal
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testSendEndSignalORMException(): void
    {

        $channel = $this->createMock(ChannelFacebook::class);

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);

        $api = $this->createMock(ChannelApiInterface::class);
        $api->expects(self::atLeastOnce())
            ->method('sendEndSignal')
            ->willThrowException(new ORMException('some error'));

        $this->stack->expects(self::atLeastOnce())
            ->method('getApiForChannel')
            ->willReturn($api);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('persist')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcastManager->sendEndSignal($streamEvent);
    }

    /**
     * Test getting planned broadcasts
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetPlannedBroadcasts(): void
    {
        $repository = $this->createMock(LiveBroadcastRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([new LiveBroadcast()]);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcasts = $broadcastManager->getPlannedBroadcasts();

        self::assertCount(1, $broadcasts);
    }

    /**
     * Test getting planned broadcasts
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetNonePlannedBroadcasts(): void
    {
        $repository = $this->createMock(LiveBroadcastRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([]);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack);
        $broadcasts = $broadcastManager->getPlannedBroadcasts();

        self::assertCount(0, $broadcasts);
    }

    /**
     * Setup mock objects
     */
    protected function setUp()
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->stack = $this->createMock(ChannelApiStack::class);
    }
}
