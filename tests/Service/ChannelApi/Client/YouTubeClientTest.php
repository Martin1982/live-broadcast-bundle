<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi\Client;

use Google\Client;
use Google\Service\Exception;
use Google\Service\YouTube;
use Google\Service\YouTube\Resource\LiveBroadcasts;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config\YouTubeConfig;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\GoogleClient;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\YouTubeClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class YouTubeClientTest
 */
class YouTubeClientTest extends TestCase
{
    /**
     * @var YouTubeConfig|MockObject
     */
    protected $config;

    /**
     * @var GoogleClient|MockObject
     */
    protected $google;

    /**
     * Test setting a channel
     *
     * @throws LiveBroadcastOutputException
     */
    public function testSetChannel(): void
    {
        $googleClient = $this->createMock(Client::class);
        $googleClient->expects(self::atLeastOnce())
            ->method('fetchAccessTokenWithRefreshToken')
            ->willReturn([]);

        $channel = $this->createMock(ChannelYouTube::class);
        $channel->expects(self::atLeastOnce())
            ->method('getRefreshToken');

        $this->google->expects(self::atLeastOnce())
            ->method('getClient')
            ->willReturn($googleClient);

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setChannel($channel);
    }

    /**
     * Test creating a broadcast
     */
    public function testCreateBroadcastThrowsException(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime('-10 minutes'));
        $broadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('Unit testing broadcast');
        $broadcast->expects(self::atLeastOnce())
            ->method('getDescription')
            ->willReturn('Unit testing broadcast description');
        $broadcast->expects(self::atLeastOnce())
            ->method('getEndTimestamp')
            ->willReturn(new \DateTime('+3 days'));
        $broadcast->expects(self::atLeastOnce())
            ->method('getPrivacyStatus')
            ->willReturn(LiveBroadcast::PRIVACY_STATUS_PRIVATE);

        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('insert')
            ->willThrowException(new Exception('The call failed'));

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->createBroadcast($broadcast);
    }

    /**
     * Test creating a broadcast
     *
     * @throws LiveBroadcastOutputException
     */
    public function testCreateBroadcast(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime('-10 minutes'));
        $broadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('Unit testing broadcast');
        $broadcast->expects(self::atLeastOnce())
            ->method('getDescription')
            ->willReturn('Unit testing broadcast description');
        $broadcast->expects(self::atLeastOnce())
            ->method('getEndTimestamp')
            ->willReturn(new \DateTime('+3 days'));
        $broadcast->expects(self::atLeastOnce())
            ->method('getPrivacyStatus')
            ->willReturn(LiveBroadcast::PRIVACY_STATUS_PUBLIC);

        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('insert')
            ->willReturn(new YouTube\LiveBroadcast());

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->createBroadcast($broadcast);
    }

    /**
     * Test ending the live-stream
     */
    public function testEndLiveStreamThrowsException(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('transition')
            ->willThrowException(new Exception('The call failed'));

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->endLiveStream(10);
    }

    /**
     * Test ending the live-stream
     *
     * @throws LiveBroadcastOutputException
     */
    public function testEndLiveStream(): void
    {
        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('transition')
            ->willReturn(new YouTube\LiveBroadcast());

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->endLiveStream(10);
    }

    /**
     * Test removing the live-stream
     */
    public function testRemoveStreamThrowsException(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('10');

        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('delete')
            ->willThrowException(new Exception('The call failed'));

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->removeLiveStream($streamEvent);
    }

    /**
     * Test removing the live-stream
     *
     * @throws LiveBroadcastOutputException
     */
    public function testRemoveStream(): void
    {
        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('10');

        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('delete')
            ->willReturn(new YouTube\LiveBroadcast());

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->removeLiveStream($streamEvent);
    }

    /**
     * Test updating a live-stream fails
     *
     * @throws LiveBroadcastOutputException
     */
    public function testWontUpdateLiveStream(): void
    {
        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getBroadcast')
            ->willReturn(null);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn(null);

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->updateLiveStream($streamEvent);
    }

    /**
     * Test updating a live-stream
     *
     * @throws LiveBroadcastOutputException
     */
    public function testUpdateLiveStream(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime('-10 minutes'));
        $broadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('Unit testing broadcast');
        $broadcast->expects(self::atLeastOnce())
            ->method('getDescription')
            ->willReturn('Unit testing broadcast description');
        $broadcast->expects(self::atLeastOnce())
            ->method('getEndTimestamp')
            ->willReturn(new \DateTime('+3 days'));

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getBroadcast')
            ->willReturn($broadcast);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('10');

        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('update')
            ->willReturn(new YouTube\LiveBroadcast());

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->updateLiveStream($streamEvent);
    }

    /**
     * Test updating a live-stream
     */
    public function testUpdateLiveStreamThrowsException(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime('-10 minutes'));
        $broadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('Unit testing broadcast');
        $broadcast->expects(self::atLeastOnce())
            ->method('getDescription')
            ->willReturn('Unit testing broadcast description');
        $broadcast->expects(self::atLeastOnce())
            ->method('getEndTimestamp')
            ->willReturn(new \DateTime('+3 days'));

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getBroadcast')
            ->willReturn($broadcast);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('10');

        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('update')
            ->willThrowException(new Exception('The call failed'));

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->updateLiveStream($streamEvent);
    }

    /**
     * Test adding a thumbnail to a broadcast with an invalid thumbnail
     */
    public function testAddThumbnailToBroadcastInvalidThumbnail(): void
    {
        $thumbFile = $this->createMock(File::class);
        $thumbFile->expects(self::atLeastOnce())
            ->method('isFile')
            ->willReturn(false);

        $youtubeBroadcast = new YouTube\LiveBroadcast();
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::exactly(2))
            ->method('getThumbnail')
            ->willReturnOnConsecutiveCalls(
                null,
                $thumbFile
            );

        $youtube = new YouTubeClient($this->config, $this->google);
        self::assertFalse($youtube->addThumbnailToBroadcast($youtubeBroadcast, $broadcast));
        self::assertFalse($youtube->addThumbnailToBroadcast($youtubeBroadcast, $broadcast));
    }

    /**
     * Test creating a stream
     */
    public function testCreateStreamThrowsException(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $liveStreams = $this->createMock(YouTube\Resource\LiveStreams::class);
        $liveStreams->expects(self::atLeastOnce())
            ->method('insert')
            ->willThrowException(new Exception('The call failed'));

        $client = $this->createMock(YouTube::class);
        $client->liveStreams = $liveStreams;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->createStream('my title');
    }

    /**
     * Test creating a stream
     *
     * @throws LiveBroadcastOutputException
     */
    public function testCreateStream(): void
    {
        $liveStreams = $this->createMock(YouTube\Resource\LiveStreams::class);
        $liveStreams->expects(self::atLeastOnce())
            ->method('insert')
            ->willReturn($this->createMock(YouTube\LiveStream::class));

        $client = $this->createMock(YouTube::class);
        $client->liveStreams = $liveStreams;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->createStream('my title');
    }

    /**
     * Test binding a stream to a broadcast
     */
    public function testBindThrowsException(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $broadcast = $this->createMock(YouTube\LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn('someId');

        $stream = $this->createMock(YouTube\LiveStream::class);

        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('bind')
            ->willThrowException(new Exception('The call failed'));

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->bind($broadcast, $stream);
    }

    /**
     * Test binding a stream to a broadcast
     *
     * @throws LiveBroadcastOutputException
     */
    public function testBind(): void
    {
        $broadcast = $this->createMock(YouTube\LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn('someId');

        $stream = $this->createMock(YouTube\LiveStream::class);

        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('bind')
            ->willReturn(new YouTube\LiveBroadcast());

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->bind($broadcast, $stream);
    }

    /**
     * Test getting a broadcast
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetYouTubeBroadcast(): void
    {
        $broadcastsService = $this->createMock(LiveBroadcasts::class);
        $listResponse = $this->createMock(YouTube\LiveBroadcastListResponse::class);
        $broadcast = $this->createMock(YouTube\LiveBroadcast::class);
        $itemsResponse = [$broadcast];

        $listResponse->expects(self::atLeastOnce())
            ->method('getItems')
            ->willReturn($itemsResponse);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('listLiveBroadcasts')
            ->willReturn($listResponse);

        $client = $this->createMock(YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->getYoutubeBroadcast('xyz');
    }

    /**
     * Test getting the stream url
     */
    public function testGetStreamUrl(): void
    {
        $ingestion = $this->createMock(YouTube\IngestionInfo::class);
        $ingestion->expects(self::atLeastOnce())
            ->method('getIngestionAddress')
            ->willReturn('rtmp://you.tu.be');
        $ingestion->expects(self::atLeastOnce())
            ->method('getStreamName')
            ->willReturn('a-stream-name');

        $cdn = $this->createMock(YouTube\CdnSettings::class);
        $cdn->expects(self::atLeastOnce())
            ->method('getIngestionInfo')
            ->willReturn($ingestion);

        $stream = $this->createMock(YouTube\LiveStream::class);
        $stream->expects(self::atLeastOnce())
            ->method('getCdn')
            ->willReturn($cdn);

        $liveStreamList = $this->createMock(YouTube\LiveStreamListResponse::class);
        $liveStreamList->expects(self::atLeastOnce())
            ->method('current')
            ->willReturn($stream);

        $liveStreams = $this->createMock(YouTube\Resource\LiveStreams::class);
        $liveStreams->expects(self::atLeastOnce())
            ->method('listLiveStreams')
            ->willReturn($liveStreamList);

        $client = $this->createMock(YouTube::class);
        $client->liveStreams = $liveStreams;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $url = $youtube->getStreamUrl('xyz');

        self::assertEquals('rtmp://you.tu.be/a-stream-name', $url);
    }

    /**
     * Setup basic mock objects
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(YouTubeConfig::class);
        $this->google = $this->createMock(GoogleClient::class);
    }
}
