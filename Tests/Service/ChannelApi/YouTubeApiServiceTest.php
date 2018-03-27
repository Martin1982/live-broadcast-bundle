<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlanableChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEventRepository;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\YouTubeClient;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class YouTubeApiServiceTest
 */
class YouTubeApiServiceTest extends TestCase
{
    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var YouTubeClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * Test creating a live event
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testCreateLiveEvent(): void
    {
        $this->entityManager->expects(self::atLeastOnce())
            ->method('persist')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('A YouTube broadcast');

        $channel = $this->createMock(ChannelYouTube::class);

        $youTubeBroadcast = $this->createMock(\Google_Service_YouTube_LiveBroadcast::class);
        $youTubeBroadcast->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn('dfdnjfds');

        $youTubeStream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

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
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testRemoveLiveEvent(): void
    {
        $this->entityManager->expects(self::atLeastOnce())
            ->method('remove')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $this->client->expects(self::atLeastOnce())
            ->method('removeLivestream')
            ->willReturn(true);

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
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testUpdateLiveEventWhenThereIsNone(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('A YouTube broadcast');

        $channel = $this->createMock(ChannelYouTube::class);

        $youTubeBroadcast = $this->createMock(\Google_Service_YouTube_LiveBroadcast::class);
        $youTubeBroadcast->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn('dfdnjfds');

        $youTubeStream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

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
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
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
            ->method('updateLiveStream')
            ->willReturn(true);

        $service = $this->getService();
        $service->updateLiveEvent($broadcast, $channel);
    }

    /**
     * Test that when no event is available an exception is thrown
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGetStreamUrlForNoEvent(): void
    {
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
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGetStreamUrl(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $contentDetails = $this->createMock(\Google_Service_YouTube_LiveBroadcastContentDetails::class);
        $contentDetails->expects(self::atLeastOnce())
            ->method('getBoundStreamId')
            ->willReturn('abcdef');

        $youTubeBroadcast = $this->createMock(\Google_Service_YouTube_LiveBroadcast::class);
        $youTubeBroadcast->expects(self::atLeastOnce())
            ->method('getContentDetails')
            ->willReturn($contentDetails);

        $event = $this->createMock(StreamEvent::class);
        $event->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('fdkkfd');

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
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testSendEndSignalOnWrongChannel()
    {
        $channel = $this->createMock(PlanableChannelInterface::class);

        $this->client->expects(self::never())
            ->method('endLiveStream')
            ->willReturn(true);

        $service = $this->getService();
        $service->sendEndSignal($channel, 'fdjj');
    }

    /**
     * Test sending an end signal
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testSendEndSignal()
    {
        $channel = $this->createMock(ChannelYouTube::class);

        $this->client->expects(self::atLeastOnce())
            ->method('endLiveStream')
            ->willReturn(true);

        $service = $this->getService();
        $service->sendEndSignal($channel, 'fdjj');
    }

    /**
     * Test that only YouTube channels are allowed
     *
     * @expectedException  \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testCantSetOtherChannel(): void
    {
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
    protected function setUp()
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->client = $this->createMock(YouTubeClient::class);
        $this->client->expects(self::any())
            ->method('setChannel')
            ->willReturn(true);
    }
}
