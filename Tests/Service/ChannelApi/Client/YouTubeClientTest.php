<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi\Client;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config\YouTubeConfig;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\GoogleClient;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\YouTubeClient;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class YouTubeClientTest
 */
class YouTubeClientTest extends TestCase
{
    use PHPMock;

    /**
     * @var YouTubeConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var GoogleClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $google;

    /**
     * Test setting a channel
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testCannotSetChannel(): void
    {
        $channel = $this->createMock(ChannelYouTube::class);
        $channel->expects(self::atLeastOnce())
            ->method('getRefreshToken');

        $this->google->expects(self::atLeastOnce())
            ->method('getClient')
            ->willReturn(null);

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setChannel($channel);
    }

    /**
     * Test setting a channel
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testSetChannel(): void
    {
        $googleClient = $this->createMock(\Google_Client::class);
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
    public function testCreateBroadcast(): void
    {
        $getimagesize = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client', 'getimagesize');
        $getimagesize->expects(static::once())
            ->willReturn([10, 20]);

        $thumbFile = $this->createMock(File::class);
        $thumbFile->expects(self::atLeastOnce())
            ->method('isFile')
            ->willReturn(true);
        $thumbFile->expects(self::atLeastOnce())
            ->method('getFilename')
            ->willReturn('somefile.jpg');
        $thumbFile->expects(self::atLeastOnce())
            ->method('getRealPath')
            ->willReturn('/nosuchdir/somefile.jpg');

        $this->config->expects(self::atLeastOnce())
            ->method('getHost')
            ->willReturn('some.host.com');
        $this->config->expects(self::atLeastOnce())
            ->method('getThumbnailDirectory')
            ->willReturn('/nosuchdir');

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
            ->method('getThumbnail')
            ->willReturn($thumbFile);

        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('insert')
            ->willReturn(new \Google_Service_YouTube_LiveBroadcast());

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->createBroadcast($broadcast);
    }

    /**
     * Test ending the live-stream
     */
    public function testEndLiveStream(): void
    {
        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('transition')
            ->willReturn(new \Google_Service_YouTube_LiveBroadcast());

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->endLiveStream(10);
    }

    /**
     * Test removing the live-stream
     */
    public function testRemoveStream(): void
    {
        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn(10);

        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('delete')
            ->willReturn(new \Google_Service_YouTube_LiveBroadcast());

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->removeLivestream($streamEvent);
    }

    /**
     * Test updating a live-stream fails
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
     */
    public function testUpdateLiveStream(): void
    {
        $getimagesize = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client', 'getimagesize');
        $getimagesize->expects(static::once())
            ->willReturn([10, 20]);

        $thumbFile = $this->createMock(File::class);
        $thumbFile->expects(self::atLeastOnce())
            ->method('isFile')
            ->willReturn(true);
        $thumbFile->expects(self::atLeastOnce())
            ->method('getFilename')
            ->willReturn('somefile.jpg');
        $thumbFile->expects(self::atLeastOnce())
            ->method('getRealPath')
            ->willReturn('/nosuchdir/somefile.jpg');

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
            ->method('getThumbnail')
            ->willReturn($thumbFile);

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getBroadcast')
            ->willReturn($broadcast);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('10');

        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('update')
            ->willReturn(new \Google_Service_YouTube_LiveBroadcast());

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->updateLiveStream($streamEvent);
    }

    /**
     * Test creating a stream
     */
    public function testCreateStream(): void
    {
        $liveStreams = $this->createMock(\Google_Service_YouTube_Resource_LiveStreams::class);
        $liveStreams->expects(self::atLeastOnce())
            ->method('insert')
            ->willReturn($this->createMock(\Google_Service_YouTube_LiveStream::class));

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveStreams = $liveStreams;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->createStream('my title');
    }

    /**
     * Test binding a stream to a broadcast
     */
    public function testBind(): void
    {
        $broadcast = $this->createMock(\Google_Service_YouTube_LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn('someId');

        $stream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('bind')
            ->willReturn(new \Google_Service_YouTube_LiveBroadcast());

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->bind($broadcast, $stream);
    }

    /**
     * Test getting a broadcast
     */
    public function testGetYouTubeBroadcast(): void
    {
        $listResponse = $this->createMock(\Google_Service_YouTube_LiveBroadcastListResponse::class);

        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('listLiveBroadcasts')
            ->willReturn($listResponse);

        $client = $this->createMock(\Google_Service_YouTube::class);
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
        $ingestion = $this->createMock(\Google_Service_YouTube_IngestionInfo::class);
        $ingestion->expects(self::atLeastOnce())
            ->method('getIngestionAddress')
            ->willReturn('rtmp://you.tu.be');
        $ingestion->expects(self::atLeastOnce())
            ->method('getStreamName')
            ->willReturn('astreamname');

        $cdn = $this->createMock(\Google_Service_YouTube_CdnSettings::class);
        $cdn->expects(self::atLeastOnce())
            ->method('getIngestionInfo')
            ->willReturn($ingestion);

        $stream = $this->createMock(\Google_Service_YouTube_LiveStream::class);
        $stream->expects(self::atLeastOnce())
            ->method('getCdn')
            ->willReturn($cdn);

        $liveStreamList = $this->createMock(\Google_Service_YouTube_LiveStreamListResponse::class);
        $liveStreamList->expects(self::atLeastOnce())
            ->method('current')
            ->willReturn($stream);

        $liveStreams = $this->createMock(\Google_Service_YouTube_Resource_LiveStreams::class);
        $liveStreams->expects(self::atLeastOnce())
            ->method('listLiveStreams')
            ->willReturn($liveStreamList);

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveStreams = $liveStreams;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $url = $youtube->getStreamUrl('xyz');

        self::assertEquals('rtmp://you.tu.be/astreamname', $url);
    }

    /**
     * Setup basic mock objects
     */
    protected function setUp()
    {
        $this->config = $this->createMock(YouTubeConfig::class);
        $this->google = $this->createMock(GoogleClient::class);
    }
}
