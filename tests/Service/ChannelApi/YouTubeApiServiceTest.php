<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Google\Service\YouTube\LiveBroadcastContentDetails;
use Google\Service\YouTube\LiveStream;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlannedChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEventRepository;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\YouTubeClient;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class YouTubeApiServiceTest
 */
class YouTubeApiServiceTest extends TestCase
{
    /**
     * @var EntityManager|MockObject
     */
    protected $entityManager;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var YouTubeClient|MockObject
     */
    protected $client;

    /**
     * Test creating a live event
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws LiveBroadcastOutputException
     */
    public function testCreateLiveEvent(): void
    {
        $this->entityManager->expects(self::atLeastOnce())
            ->method('persist')
            ->willReturn(true);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('A YouTube broadcast');

        $channel = $this->createMock(ChannelYouTube::class);

        $youTubeBroadcast = $this->createMock(\Google\Service\YouTube\LiveBroadcast::class);
        $youTubeBroadcast->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn('broadcast-id');

        $youTubeStream = $this->createMock(LiveStream::class);

        $this->client->expects(self::atLeastOnce())
            ->method('createBroadcast')
            ->willReturn($youTubeBroadcast);
        $this->client->expects(self::atLeastOnce())
            ->method('createStream')
            ->willReturn($youTubeStream);
        $this->client->expects(self::atLeastOnce())
            ->method('bind')
            ->willReturn($youTubeBroadcast);

        $service = $this->getService();
        $service->createLiveEvent($broadcast, $channel);
    }

    /**
     * Test removing a live event
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws LiveBroadcastOutputException
     */
    public function testRemoveLiveEvent(): void
    {
        $this->entityManager->expects(self::atLeastOnce())
            ->method('remove');

        $this->client->expects(self::atLeastOnce())
            ->method('removeLiveStream');

        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $event = $this->createMock(StreamEvent::class);

        $repository = $this->createMock(StreamEventRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findBroadcastingToChannel')
            ->willReturn($event);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $service = $this->getService();
        $service->removeLiveEvent($broadcast, $channel);
    }

    /**
     * Test that a live event gets created when there is none
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws LiveBroadcastOutputException
     */
    public function testUpdateLiveEventWhenThereIsNone(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('A YouTube broadcast');

        $channel = $this->createMock(ChannelYouTube::class);

        $youTubeBroadcast = $this->createMock(\Google\Service\YouTube\LiveBroadcast::class);
        $youTubeBroadcast->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn('broadcast-id');

        $youTubeStream = $this->createMock(LiveStream::class);

        $this->client->expects(self::atLeastOnce())
            ->method('createBroadcast')
            ->willReturn($youTubeBroadcast);
        $this->client->expects(self::atLeastOnce())
            ->method('createStream')
            ->willReturn($youTubeStream);
        $this->client->expects(self::atLeastOnce())
            ->method('bind')
            ->willReturn($youTubeBroadcast);

        $repository = $this->createMock(StreamEventRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findBroadcastingToChannel')
            ->willReturn(null);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $service = $this->getService();
        $service->updateLiveEvent($broadcast, $channel);
    }

    /**
     * Test that an event gets updated
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws LiveBroadcastOutputException
     */
    public function testUpdateLiveEvent(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $event = $this->createMock(StreamEvent::class);

        $repository = $this->createMock(StreamEventRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findBroadcastingToChannel')
            ->willReturn($event);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $this->client->expects(self::atLeastOnce())
            ->method('updateLiveStream');

        $service = $this->getService();
        $service->updateLiveEvent($broadcast, $channel);
    }

    /**
     * Test that when no event is available an exception is thrown
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetStreamUrlForNoEvent(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $repository = $this->createMock(StreamEventRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findBroadcastingToChannel')
            ->willReturn(null);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $service = $this->getService();
        $service->getStreamUrl($broadcast, $channel);
    }

    /**
     * Test getting a stream url
     *
     * @throws LiveBroadcastOutputException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetStreamUrl(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $contentDetails = $this->createMock(LiveBroadcastContentDetails::class);
        $contentDetails->expects(self::atLeastOnce())
            ->method('getBoundStreamId')
            ->willReturn('bound-stream-id');

        $youTubeBroadcast = $this->createMock(\Google\Service\YouTube\LiveBroadcast::class);
        $youTubeBroadcast->expects(self::atLeastOnce())
            ->method('getContentDetails')
            ->willReturn($contentDetails);

        $event = $this->createMock(StreamEvent::class);
        $event->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('external-stream-id');

        $repository = $this->createMock(StreamEventRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findBroadcastingToChannel')
            ->willReturn($event);

        $this->client->expects(self::atLeastOnce())
            ->method('getYoutubeBroadcast')
            ->willReturn($youTubeBroadcast);
        $this->client->expects(self::atLeastOnce())
            ->method('getStreamUrl')
            ->willReturn('rtmp://you.tu.be/abc');

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $service = $this->getService();
        $url = $service->getStreamUrl($broadcast, $channel);

        self::assertEquals('rtmp://you.tu.be/abc', $url);
    }

    /**
     * Test sending an end signal to a wrong channel type
     *
     * @throws LiveBroadcastOutputException
     */
    public function testSendEndSignalOnWrongChannel(): void
    {
        $channel = $this->createMock(PlannedChannelInterface::class);

        $this->client->expects(self::never())
            ->method('endLiveStream');

        $service = $this->getService();
        $service->sendEndSignal($channel, 'external-id');
    }

    /**
     * Test sending an end signal
     *
     * @throws LiveBroadcastOutputException
     */
    public function testSendEndSignal(): void
    {
        $channel = $this->createMock(ChannelYouTube::class);

        $this->client->expects(self::atLeastOnce())
            ->method('endLiveStream');

        $service = $this->getService();
        $service->sendEndSignal($channel, 'external-id');
    }

    /**
     * Test that only YouTube channels are allowed
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testCantSetOtherChannel(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelFacebook::class);

        $service = $this->getService();
        $service->removeLiveEvent($broadcast, $channel);
    }

    /**
     * @return YouTubeApiService
     */
    protected function getService(): YouTubeApiService
    {
        return new YouTubeApiService($this->entityManager, $this->logger, $this->client);
    }

    /**
     * Setup basic mocks
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->client = $this->createMock(YouTubeClient::class);
    }
}
