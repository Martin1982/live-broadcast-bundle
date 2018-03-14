<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEventRepository;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastLoopEvent;
use Martin1982\LiveBroadcastBundle\EventListener\YouTubePostBroadcastLoopListener;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use PHPUnit\Framework\TestCase;
use phpmock\phpunit\PHPMock;

/**
 * Class YouTubePostBroadcastLoopListenerTest
 */
class YouTubePostBroadcastLoopListenerTest extends TestCase
{
    use PHPMock;

    /**
     * Test event when a broadcast loop has ended
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testOnPostBroadcastLoop(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $redirect = $this->createMock(GoogleRedirectService::class);
        $event = $this->createMock(PostBroadcastLoopEvent::class);

        $stream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

        $api = $this->createMock(YouTubeApiService::class);
        $api->expects(static::any())
            ->method('getBroadcastStatus')
            ->willReturnOnConsecutiveCalls(
                YouTubeEvent::STATE_REMOTE_CREATED,
                YouTubeEvent::STATE_REMOTE_TESTING,
                YouTubeEvent::STATE_REMOTE_LIVE,
                YouTubeEvent::STATE_REMOTE_CREATED,
                YouTubeEvent::STATE_REMOTE_TESTING,
                YouTubeEvent::STATE_REMOTE_LIVE,
                YouTubeEvent::STATE_REMOTE_CREATED,
                YouTubeEvent::STATE_REMOTE_TESTING,
                YouTubeEvent::STATE_REMOTE_LIVE
            );
        $api->expects(static::any())
            ->method('getStream')
            ->willReturn($stream);
        $api->expects(static::any())
            ->method('getStreamUrl')
            ->willReturn('rtmp://stream.to.me');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(static::any())
            ->method('info')
            ->willReturn(true);

        $commands = $this->createMock(SchedulerCommandsInterface::class);
        $commands->expects(static::any())
            ->method('getRunningProcesses')
            ->willReturn([
                // @codingStandardsIgnoreLine
                'someuser   44558   ffmpeg /somedir/somefile.mp4 rtmp://youtu.be/streamurl -metadata broadcast_id=1 -metadata channel_id=2 -metadata env=test -metadata monitor_stream=yes /dev/null',
                // @codingStandardsIgnoreLine
                'someuser   44559   ffmpeg /somedir/somefile.mp4 rtmp://youtu.be/streamurl -metadata broadcast_id=4 -metadata channel_id=2 -metadata env=test -metadata monitor_stream=yes /dev/null',
            ]);

        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $eventPlanned = new YouTubeEvent();
        $eventPlanned->setBroadcast($broadcast);
        $eventPlanned->setChannel($channel);
        $eventPlanned->setYouTubeId('abc123');

        $eventTesting = new YouTubeEvent();
        $eventTesting->setBroadcast($broadcast);
        $eventTesting->setChannel($channel);
        $eventTesting->setYouTubeId('def456');

        $eventLive = new YouTubeEvent();
        $eventLive->setBroadcast($broadcast);
        $eventLive->setChannel($channel);
        $eventLive->setYouTubeId('ghi789');

        $testableEvents = [
            $eventPlanned,
            $eventTesting,
            $eventLive,
        ];

        $eventRepository = $this->createMock(YouTubeEventRepository::class);
        $eventRepository->expects(static::any())
            ->method('getTestableEvents')
            ->willReturn($testableEvents);

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects(static::any())
            ->method('getRepository')
            ->willReturn($eventRepository);
        $entityManager->expects(static::any())
            ->method('persist')
            ->willReturn(true);
        $entityManager->expects(static::any())
            ->method('flush')
            ->willReturn(true);

        $fileExists = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\StreamInput', 'file_exists');
        $fileExists->expects(static::any())
            ->willReturn(true);

        $listener = new YouTubePostBroadcastLoopListener($entityManager, $commands, $api, $kernel, $redirect, $logger);
        $listener->onPostBroadcastLoop($event);
        $this->addToAssertionCount(1);
    }

    /**
     * Test event availability
     */
    public function testGetSubscribedEvents(): void
    {
        $events = YouTubePostBroadcastLoopListener::getSubscribedEvents();
        self::assertArrayHasKey(PostBroadcastLoopEvent::NAME, $events);
    }
}
