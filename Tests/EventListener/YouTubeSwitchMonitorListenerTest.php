<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia;
use Martin1982\LiveBroadcastBundle\Event\SwitchMonitorEvent;
use Martin1982\LiveBroadcastBundle\EventListener\YouTubeSwitchMonitorListener;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputInterface;
use Martin1982\LiveBroadcastBundle\Service\StreamInputService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class YouTubeSwitchMonitorListenerTest
 */
class YouTubeSwitchMonitorListenerTest extends TestCase
{
    /**
     * Test switching without a stream
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     *
     * @throws LiveBroadcastOutputException
     */
    public function testSwitchWithoutStream(): void
    {
        $commandsInterface = $this->createMock(SchedulerCommandsInterface::class);
        $commandsInterface->expects(static::any())
            ->method('stopProcess')
            ->willReturn(true);

        $outputInterface = $this->createMock(OutputYouTube::class);

        $output = $this->createMock(StreamOutputService::class);
        $output->expects(static::any())
            ->method('getOutputInterface')
            ->willReturn($outputInterface);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects(static::any())
            ->method('generateInputCmd')
            ->willReturn('some input command');

        $input = $this->createMock(StreamInputService::class);
        $input->expects(static::any())
            ->method('getInputInterface')
            ->willReturn($inputInterface);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(static::any())
            ->method('info')
            ->willReturn(true);

        $api = $this->createMock(YouTubeApiService::class);
        $api->expects(static::any())
            ->method('initApiClients')
            ->willReturn(true);
        $api->expects(static::any())
            ->method('transitionState')
            ->willReturn(true);
        $api->expects(static::any())
            ->method('getStream')
            ->willReturn(null);

        $redirect = $this->createMock(GoogleRedirectService::class);
        $redirect->expects(static::any())
            ->method('getOAuthRedirectUrl')
            ->willReturn('http://redirect.url');

        $monitorBroadcast = $this->createMock(RunningBroadcast::class);
        $monitorBroadcast->expects(static::any())
            ->method('getBroadcastId')
            ->willReturn('10');
        $monitorBroadcast->expects(static::any())
            ->method('getProcessId')
            ->willReturn('55834');

        $plannedInput = $this->createMock(AbstractMedia::class);

        $plannedBroadcast = $this->createMock(LiveBroadcast::class);
        $plannedBroadcast->expects(static::any())
            ->method('getInput')
            ->willReturn($plannedInput);
        $plannedBroadcast->expects(static::any())
            ->method('getBroadcastId')
            ->willReturn('28');

        $youtubeChannel = $this->createMock(ChannelYouTube::class);

        $event = $this->createMock(SwitchMonitorEvent::class);
        $event->expects(static::any())
            ->method('getMonitorBroadcast')
            ->willReturn($monitorBroadcast);
        $event->expects(static::any())
            ->method('getPlannedBroadcast')
            ->willReturn($plannedBroadcast);
        $event->expects(static::any())
            ->method('getChannel')
            ->willReturn($youtubeChannel);

        $listener = new YouTubeSwitchMonitorListener($commandsInterface, $output, $input, $api, $redirect, $logger);
        $listener->onSwitchMonitor($event);
    }

    /**
     * Test switching a monitor stream
     *
     * @throws LiveBroadcastOutputException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testOnSwitchMonitor(): void
    {
        $commandsInterface = $this->createMock(SchedulerCommandsInterface::class);
        $commandsInterface->expects(static::any())
            ->method('stopProcess')
            ->willReturn(true);
        $commandsInterface->expects(static::any())
            ->method('startProcess')
            ->willReturn(true);

        $outputInterface = $this->createMock(OutputYouTube::class);
        $outputInterface->expects(static::any())
            ->method('generateOutputCmd')
            ->willReturn('output command');
        $outputInterface->expects(static::any())
            ->method('setStreamUrl')
            ->willReturn(true);

        $output = $this->createMock(StreamOutputService::class);
        $output->expects(static::any())
            ->method('getOutputInterface')
            ->willReturn($outputInterface);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects(static::any())
            ->method('generateInputCmd')
            ->willReturn('some input command');

        $input = $this->createMock(StreamInputService::class);
        $input->expects(static::any())
            ->method('getInputInterface')
            ->willReturn($inputInterface);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(static::any())
            ->method('info')
            ->willReturn(true);

        $stream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

        $api = $this->createMock(YouTubeApiService::class);
        $api->expects(static::any())
            ->method('initApiClients')
            ->willReturn(true);
        $api->expects(static::any())
            ->method('transitionState')
            ->willReturn(true);
        $api->expects(static::any())
            ->method('getStream')
            ->willReturn($stream);
        $api->expects(static::any())
            ->method('getStreamUrl')
            ->willReturn('rtmp://stream.url');

        $redirect = $this->createMock(GoogleRedirectService::class);
        $redirect->expects(static::any())
            ->method('getOAuthRedirectUrl')
            ->willReturn('http://redirect.url');

        $monitorBroadcast = $this->createMock(RunningBroadcast::class);
        $monitorBroadcast->expects(static::any())
            ->method('getBroadcastId')
            ->willReturn('10');
        $monitorBroadcast->expects(static::any())
            ->method('getProcessId')
            ->willReturn('55834');

        $plannedInput = $this->createMock(AbstractMedia::class);

        $plannedBroadcast = $this->createMock(LiveBroadcast::class);
        $plannedBroadcast->expects(static::any())
            ->method('getInput')
            ->willReturn($plannedInput);
        $plannedBroadcast->expects(static::any())
            ->method('getBroadcastId')
            ->willReturn('28');

        $youtubeChannel = $this->createMock(ChannelYouTube::class);

        $event = $this->createMock(SwitchMonitorEvent::class);
        $event->expects(static::any())
            ->method('getMonitorBroadcast')
            ->willReturn($monitorBroadcast);
        $event->expects(static::any())
            ->method('getPlannedBroadcast')
            ->willReturn($plannedBroadcast);
        $event->expects(static::any())
            ->method('getChannel')
            ->willReturn($youtubeChannel);

        $listener = new YouTubeSwitchMonitorListener($commandsInterface, $output, $input, $api, $redirect, $logger);
        $listener->onSwitchMonitor($event);
        $this->addToAssertionCount(1);
    }

    /**
     * Test events are registered
     */
    public function testGetSubscribedEvents(): void
    {
        $events = YouTubeSwitchMonitorListener::getSubscribedEvents();
        self::assertArrayHasKey(SwitchMonitorEvent::NAME, $events);
    }
}
