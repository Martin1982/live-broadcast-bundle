<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcastRepository;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEventRepository;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastApiException;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiInterface;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiStack;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class BroadcastManagerTest
 */
class BroadcastManagerTest extends TestCase
{
    /**
     * @var EntityManager|MockObject
     */
    protected $entityManager;

    /**
     * @var ChannelApiStack|MockObject
     */
    protected $stack;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * Test getting a broadcast entity by id
     * @throws Exception
     * @throws Exception
     */
    public function testGetBroadcastById(): void
    {
        $broadcastEntity = $this->createMock(LiveBroadcast::class);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(static::atLeastOnce())
            ->method('findOneBy')
            ->with([ 'broadcastId' => 10 ])
            ->willReturn($broadcastEntity);

        $this->entityManager->expects(static::atLeastOnce())
            ->method('getRepository')
            ->willReturn($broadcastRepository);

        $broadcast = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $result = $broadcast->getBroadcastById('10');

        self::assertInstanceOf(LiveBroadcast::class, $result);
    }

    /**
     * Test that pre-inserts get executed
     * @throws LiveBroadcastApiException
     * @throws Exception
     * @throws Exception
     * @throws Exception
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

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcastManager->preInsert($broadcast);
    }

    /**
     * Test pre-update events
     * @throws LiveBroadcastApiException
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
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
        $broadcastNewState->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);
        $broadcastOldState = $this->createMock(LiveBroadcast::class);
        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $api = $this->createMock(FacebookApiService::class);

        $oldChannelList = new ArrayCollection();
        $oldChannelList->add($fbSpinninDeep);
        $oldChannelList->add($ytSpinninRecords);
        $oldChannelList->add($ytSpinninDeep);

        $newChannelList = new ArrayCollection();
        $newChannelList->add($fbSpinninRecords);
        $newChannelList->add($ytSpinninRecords);

        $api->expects(self::atLeastOnce())
            ->method('createLiveEvent');

        $api->expects(self::atLeastOnce())
            ->method('updateLiveEvent');

        $api->expects(self::atLeastOnce())
            ->method('removeLiveEvent');

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

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcastManager->preUpdate($broadcastNewState);
    }

    /**
     * Test that no updates are done when there is no previous state
     * @throws LiveBroadcastApiException
     * @throws Exception
     * @throws Exception
     */
    public function testUpdateWithNoPreviousState():void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->with(LiveBroadcast::class)
            ->willReturn($broadcastRepository);

        $this->stack->expects(self::never())
            ->method('getApiForChannel');

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcastManager->preUpdate($broadcast);
    }

    /**
     * Test pre delete actions
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function testPreDelete(): void
    {
        $channels = new ArrayCollection([ $this->createMock(ChannelFacebook::class) ]);

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

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcastManager->preDelete($broadcast);
    }

    /**
     * Test sending an end signal
     *
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws LiveBroadcastException
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
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('some id');

        $this->stack->expects(self::atLeastOnce())
            ->method('getApiForChannel')
            ->willReturn($api);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('persist')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcastManager->sendEndSignal($streamEvent);
    }

    /**
     * Test getting an events repository
     * @throws Exception
     */
    public function testGetEventsRepository(): void
    {
        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->with(StreamEvent::class)
            ->willReturn($this->createMock(StreamEventRepository::class));

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        self::assertInstanceOf(EntityRepository::class, $broadcastManager->getEventsRepository());
    }

    /**
     * Test sending an end signal
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws LiveBroadcastException
     */
    public function testSendEndSignalLockException(): void
    {
        $this->expectException(LiveBroadcastException::class);

        $channel = $this->createMock(ChannelFacebook::class);

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('some id');

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

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcastManager->sendEndSignal($streamEvent);
    }

    /**
     * Test sending an end signal
     *
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws LiveBroadcastException
     */
    public function testSendEndSignalArgumentException(): void
    {
        $this->expectException(LiveBroadcastException::class);

        $channel = $this->createMock(ChannelFacebook::class);

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('some id');

        $api = $this->createMock(ChannelApiInterface::class);
        $api->expects(self::atLeastOnce())
            ->method('sendEndSignal')
            ->willThrowException(new LiveBroadcastOutputException('some error'));

        $this->stack->expects(self::atLeastOnce())
            ->method('getApiForChannel')
            ->willReturn($api);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('persist')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcastManager->sendEndSignal($streamEvent);
    }

    /**
     * Test sending an end signal
     *
     * @throws LiveBroadcastException
     * @throws LiveBroadcastApiException
     * @throws Exception
     */
    public function testSendEndSignalORMException(): void
    {
        $this->expectException(LiveBroadcastException::class);

        $channel = $this->createMock(ChannelFacebook::class);

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('some id');

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

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcastManager->sendEndSignal($streamEvent);
    }

    /**
     * Test getting planned broadcasts
     *
     * @throws Exception
     * @throws LiveBroadcastException
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

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcasts = $broadcastManager->getPlannedBroadcasts();

        self::assertCount(1, $broadcasts);
    }

    /**
     * Test getting planned broadcasts
     *
     * @throws LiveBroadcastException
     * @throws Exception
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

        $broadcastManager = new BroadcastManager($this->entityManager, $this->stack, $this->logger);
        $broadcasts = $broadcastManager->getPlannedBroadcasts();

        self::assertCount(0, $broadcasts);
    }

    /**
     * Setup mock objects
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->stack = $this->createMock(ChannelApiStack::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }
}
