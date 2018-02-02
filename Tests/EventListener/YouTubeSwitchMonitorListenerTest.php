<?php
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Media\BaseMedia;
use Martin1982\LiveBroadcastBundle\Event\SwitchMonitorEvent;
use Martin1982\LiveBroadcastBundle\EventListener\YouTubeSwitchMonitorListener;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputInterface;
use Martin1982\LiveBroadcastBundle\Service\StreamInputService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
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
     */
    public function testSwitchWithoutStream()
    {
        $commandsInterface = $this->createMock(SchedulerCommandsInterface::class);
        $commandsInterface->expects($this->any())
            ->method('stopProcess')
            ->willReturn(true);

        $outputInterface = $this->createMock(OutputYouTube::class);

        $output = $this->createMock(StreamOutputService::class);
        $output->expects($this->any())
            ->method('getOutputInterface')
            ->willReturn($outputInterface);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->any())
            ->method('generateInputCmd')
            ->willReturn('some input command');

        $input = $this->createMock(StreamInputService::class);
        $input->expects($this->any())
            ->method('getInputInterface')
            ->willReturn($inputInterface);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->any())
            ->method('info')
            ->willReturn(true);

        $stream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

        $api = $this->createMock(YouTubeApiService::class);
        $api->expects($this->any())
            ->method('initApiClients')
            ->willReturn(true);
        $api->expects($this->any())
            ->method('transitionState')
            ->willReturn(true);
        $api->expects($this->any())
            ->method('getStream')
            ->willReturn(null);

        $redirect = $this->createMock(GoogleRedirectService::class);
        $redirect->expects($this->any())
            ->method('getOAuthRedirectUrl')
            ->willReturn('http://redirect.url');

        $monitorBroadcast = $this->createMock(RunningBroadcast::class);
        $monitorBroadcast->expects($this->any())
            ->method('getBroadcastId')
            ->willReturn('10');
        $monitorBroadcast->expects($this->any())
            ->method('getProcessId')
            ->willReturn('55834');

        $plannedInput = $this->createMock(BaseMedia::class);

        $plannedBroadcast = $this->createMock(LiveBroadcast::class);
        $plannedBroadcast->expects($this->any())
            ->method('getInput')
            ->willReturn($plannedInput);
        $plannedBroadcast->expects($this->any())
            ->method('getBroadcastId')
            ->willReturn('28');

        $youtubeChannel = $this->createMock(ChannelYouTube::class);

        $event = $this->createMock(SwitchMonitorEvent::class);
        $event->expects($this->any())
            ->method('getMonitorBroadcast')
            ->willReturn($monitorBroadcast);
        $event->expects($this->any())
            ->method('getPlannedBroadcast')
            ->willReturn($plannedBroadcast);
        $event->expects($this->any())
            ->method('getChannel')
            ->willReturn($youtubeChannel);

        $listener = new YouTubeSwitchMonitorListener($commandsInterface, $output, $input, $api, $redirect, $logger);
        $listener->onSwitchMonitor($event);
    }

    /**
     * Test switching a monitor stream
     */
    public function testOnSwitchMonitor()
    {
        $commandsInterface = $this->createMock(SchedulerCommandsInterface::class);
        $commandsInterface->expects($this->any())
            ->method('stopProcess')
            ->willReturn(true);
        $commandsInterface->expects($this->any())
            ->method('startProcess')
            ->willReturn(true);

        $outputInterface = $this->createMock(OutputYouTube::class);
        $outputInterface->expects($this->any())
            ->method('generateOutputCmd')
            ->willReturn('output command');
        $outputInterface->expects($this->any())
            ->method('setStreamUrl')
            ->willReturn(true);

        $output = $this->createMock(StreamOutputService::class);
        $output->expects($this->any())
            ->method('getOutputInterface')
            ->willReturn($outputInterface);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects($this->any())
            ->method('generateInputCmd')
            ->willReturn('some input command');

        $input = $this->createMock(StreamInputService::class);
        $input->expects($this->any())
            ->method('getInputInterface')
            ->willReturn($inputInterface);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->any())
            ->method('info')
            ->willReturn(true);

        $stream = $this->createMock(\Google_Service_YouTube_LiveStream::class);

        $api = $this->createMock(YouTubeApiService::class);
        $api->expects($this->any())
            ->method('initApiClients')
            ->willReturn(true);
        $api->expects($this->any())
            ->method('transitionState')
            ->willReturn(true);
        $api->expects($this->any())
            ->method('getStream')
            ->willReturn($stream);
        $api->expects($this->any())
            ->method('getStreamUrl')
            ->willReturn('rtmp://stream.url');

        $redirect = $this->createMock(GoogleRedirectService::class);
        $redirect->expects($this->any())
            ->method('getOAuthRedirectUrl')
            ->willReturn('http://redirect.url');

        $monitorBroadcast = $this->createMock(RunningBroadcast::class);
        $monitorBroadcast->expects($this->any())
            ->method('getBroadcastId')
            ->willReturn('10');
        $monitorBroadcast->expects($this->any())
            ->method('getProcessId')
            ->willReturn('55834');

        $plannedInput = $this->createMock(BaseMedia::class);

        $plannedBroadcast = $this->createMock(LiveBroadcast::class);
        $plannedBroadcast->expects($this->any())
            ->method('getInput')
            ->willReturn($plannedInput);
        $plannedBroadcast->expects($this->any())
            ->method('getBroadcastId')
            ->willReturn('28');

        $youtubeChannel = $this->createMock(ChannelYouTube::class);

        $event = $this->createMock(SwitchMonitorEvent::class);
        $event->expects($this->any())
            ->method('getMonitorBroadcast')
            ->willReturn($monitorBroadcast);
        $event->expects($this->any())
            ->method('getPlannedBroadcast')
            ->willReturn($plannedBroadcast);
        $event->expects($this->any())
            ->method('getChannel')
            ->willReturn($youtubeChannel);

        $listener = new YouTubeSwitchMonitorListener($commandsInterface, $output, $input, $api, $redirect, $logger);
        $listener->onSwitchMonitor($event);
        $this->addToAssertionCount(1);
    }

    /**
     * Test events are registered
     */
    public function testGetSubscribedEvents()
    {
        $events = YouTubeSwitchMonitorListener::getSubscribedEvents();
        self::assertArrayHasKey(SwitchMonitorEvent::NAME, $events);
    }
}
