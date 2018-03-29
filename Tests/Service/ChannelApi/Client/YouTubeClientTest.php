<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi\Client;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
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
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testCreateBroadcastThrowsException(): void
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

        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('insert')
            ->willThrowException(new \Google_Service_Exception('The call failed'));

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->createBroadcast($broadcast);
    }

    /**
     * Test creating a broadcast
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
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
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testEndLiveStreamThrowsException(): void
    {
        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('transition')
            ->willThrowException(new \Google_Service_Exception('The call failed'));

        $client = $this->createMock(\Google_Service_YouTube::class);
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
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testRemoveStreamThrowsException(): void
    {
        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn(10);

        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('delete')
            ->willThrowException(new \Google_Service_Exception('The call failed'));

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->removeLivestream($streamEvent);
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
     * Test updating a live-stream
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testUpdateLiveStreamThrowsException(): void
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

        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('update')
            ->willThrowException(new \Google_Service_Exception('The call failed'));

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->liveBroadcasts = $broadcastsService;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);
        $youtube->updateLiveStream($streamEvent);
    }

    /**
     * Test adding a thumbnail to a broadcast with an invalid thumbnail
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testAddThumbnailToBroadcastInvalidThumbnail(): void
    {
        $thumbFile = $this->createMock(File::class);
        $thumbFile->expects(self::atLeastOnce())
            ->method('isFile')
            ->willReturn(false);

        $youtubeBroadcast = new \Google_Service_YouTube_LiveBroadcast();
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
     * Test adding a thumbnail to a broadcast
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testAddThumbnailToBroadcast(): void
    {
        $thumbFile = $this->createMock(File::class);
        $thumbFile->expects(self::atLeastOnce())
            ->method('isFile')
            ->willReturn(true);
        $thumbFile->expects(self::atLeastOnce())
            ->method('getRealPath')
            ->willReturn('/tmp/thumbfile.png');

        $mimeContentType = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client', 'mime_content_type');
        $mimeContentType->expects(static::once())
            ->willReturn('image/png');

        $fileSize = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client', 'filesize');
        $fileSize->expects(static::once())
            ->willReturn(500);

        $fileSize = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client', 'fopen');
        $fileSize->expects(static::once())
            ->with('/tmp/thumbfile.png')
            ->willReturn(1);

        $feof = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client', 'feof');
        $feof->expects(static::once())
            ->with(1)
            ->willReturn(false);

        $fread = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client', 'fread');
        $fread->expects(static::once())
            ->with(1, 1048576)
            ->willReturn(false);

        $fclose = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client', 'fclose');
        $fclose->expects(static::once())
            ->with(1);

        $youtubeBroadcast = new \Google_Service_YouTube_LiveBroadcast();
        $youtubeBroadcast->setId('youtube.id');

        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getThumbnail')
            ->willReturn($thumbFile);

        $googleClient = $this->createMock(\Google_Client::class);
        $googleClient->expects(self::atLeastOnce())
            ->method('setDefer')
            ->withConsecutive(
                [true],
                [false]
            );

        $googleClient->expects(self::atLeastOnce())
            ->method('execute')
            ->willReturn(new Response(200, ['location' => 'test']));

        $this->google->expects(self::atLeastOnce())
            ->method('getClient')
            ->willReturn($googleClient);

        $thumbnails = $this->createMock(\Google_Service_YouTube_Resource_Thumbnails::class);
        $thumbnails->expects(self::atLeastOnce())
            ->method('set')
            ->with('youtube.id')
            ->willReturn(new Request('get', 'test_upload'));

        $client = $this->createMock(\Google_Service_YouTube::class);
        $client->thumbnails = $thumbnails;

        $youtube = new YouTubeClient($this->config, $this->google);
        $youtube->setYouTubeClient($client);

        self::assertTrue($youtube->addThumbnailToBroadcast($youtubeBroadcast, $broadcast));
    }

    /**
     * Test creating a stream
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testCreateStreamThrowsException(): void
    {
        $liveStreams = $this->createMock(\Google_Service_YouTube_Resource_LiveStreams::class);
        $liveStreams->expects(self::atLeastOnce())
            ->method('insert')
            ->willThrowException(new \Google_Service_Exception('The call failed'));

        $client = $this->createMock(\Google_Service_YouTube::class);
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
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testBindThrowsException(): void
    {
        $broadcast = $this->createMock(\Google_Service_YouTube_LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn('someId');

        $stream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

        $broadcastsService = $this->createMock(\Google_Service_YouTube_Resource_LiveBroadcasts::class);
        $broadcastsService->expects(self::atLeastOnce())
            ->method('bind')
            ->willThrowException(new \Google_Service_Exception('The call failed'));

        $client = $this->createMock(\Google_Service_YouTube::class);
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
