<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEventRepository;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class YouTubeApiServiceTest
 */
class YouTubeApiServiceTest extends TestCase
{
    /**
     * @var string
     */
    protected $clientId = 'clientId';

    /**
     * @var string
     */
    protected $clientSecret = 'clientSecret';

    /**
     * @var string
     */
    protected $host = 'testhost';

    /**
     * @var string
     */
    protected $thumbDir = '/some/image/dir';

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var YouTubeEventRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var GoogleRedirectService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectService;

    /**
     * Test retrieving a stream url
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGetStreamUrl(): void
    {
        $ingestionInfo = $this->createMock(\Google_Service_YouTube_IngestionInfo::class);
        $ingestionInfo->expects(static::any())
            ->method('getIngestionAddress')
            ->willReturn('rtmp://some.server');
        $ingestionInfo->expects(static::any())
            ->method('getStreamName')
            ->willReturn('abcstream');

        $cdn = $this->createMock(\Google_Service_YouTube_CdnSettings::class);
        $cdn->expects(static::any())
            ->method('getIngestionInfo')
            ->willReturn($ingestionInfo);

        $liveStream = $this->createMock(\Google_Service_YouTube_LiveStream::class);
        $liveStream->expects(static::any())
            ->method('getCdn')
            ->willReturn($cdn);

        $api = new YouTubeApiService(
            $this->clientId,
            $this->clientSecret,
            $this->host,
            $this->thumbDir,
            $this->entityManager,
            $this->logger,
            $this->redirectService
        );
        $url = $api->getStreamUrl($liveStream);

        self::assertEquals('rtmp://some.server/abcstream', $url);
    }

    /**
     * Test that you cannot use the service without an app id or client secret
     *
     * @expectedException  \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testCannotLoginApp(): void
    {
        $api = new YouTubeApiService(
            '',
            '',
            $this->host,
            $this->thumbDir,
            $this->entityManager,
            $this->logger,
            $this->redirectService
        );
        $api->initApiClients('http://some.url.to');
    }

    /**
     * Test changing the YouTube Event state
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testTransitionState(): void
    {
        $googleClient = $this->createMock(\Google_Client::class);
        $youtubeClient = $this->createMock(\Google_Service_YouTube::class);
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);
        $channel->setRefreshToken('abcdef');

        $state = YouTubeEvent::STATE_LOCAL_READY;

        $api = new YouTubeApiService(
            $this->clientId,
            $this->clientSecret,
            $this->host,
            $this->thumbDir,
            $this->entityManager,
            $this->logger,
            $this->redirectService
        );
        $api->initApiClients('http://some.url.to');
        $api->setGoogleApiClient($googleClient);
        $api->setYouTubeApiClient($youtubeClient);
        $api->transitionState($broadcast, $channel, $state);
        $this->addToAssertionCount(1);
    }

    /**
     * Setup mock objects
     */
    protected function setUp()
    {
        $this->repository = $this->createMock(YouTubeEventRepository::class);

        $this->entityManager = $this->createMock(EntityManager::class);
        $this->entityManager->expects(static::any())
            ->method('getRepository')
            ->with(YouTubeEvent::class)
            ->willReturn($this->repository);
        $this->entityManager->expects(static::any())
            ->method('persist')
            ->willReturn(true);
        $this->entityManager->expects(static::any())
            ->method('remove')
            ->willReturn(true);
        $this->entityManager->expects(static::any())
            ->method('flush')
            ->willReturn(true);

        $this->redirectService = $this->createMock(GoogleRedirectService::class);
        $this->redirectService->expects(static::any())
            ->method('getOAuthRedirectUrl')
            ->willReturn('https://some.redirect.url');

        $this->logger = $this->createMock(LoggerInterface::class);
    }
}
