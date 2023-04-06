<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SchedulerCommandsTest
 */
class SchedulerCommandsTest extends TestCase
{
    /**
     * Test the start process command.
     *
     * @throws \Exception
     */
    public function testStartProcess(): void
    {
        $command = $this->getSchedulerCommands();
        $output = $command->startProcess('input', 'output', ['broadcast_id' => 4, 'unit' => 'test']);
        self::assertEquals('ffmpeg input output -metadata broadcast_id=4 -metadata unit=test -metadata env=unit_test >> /dev/null 2>&1 &', $output);
    }

    /**
     * Test the start process command with a log directory configured
     *
     * @throws \Exception
     */
    public function testStartProcessWithLogDirectory(): void
    {
        $command = $this->getSchedulerCommands();
        $command->setFFMpegLogDirectory('/tmp');
        $output = $command->startProcess('input', 'output', ['broadcast_id' => 12, 'test' => 'unit']);

        $now = new \DateTime();
        self::assertStringStartsWith('ffmpeg input output -metadata broadcast_id=12 -metadata test=unit -metadata env=unit_test >> /tmp/livebroadcaster-ffmpeg-'.$now->format('Y-m-d_Hi'), $output);
        self::assertStringEndsWith('.log 2>&1 &', $output);
    }

    /**
     * Test the stop process command.
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testStopProcess(): void
    {
        $command = $this->getSchedulerCommands();
        $output = $command->stopProcess(1337);
        self::assertEquals('kill 1337', $output);
    }

    /**
     * Test the running processes command.
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetRunningProcesses(): void
    {
        $command = $this->getSchedulerCommands();
        $running = $command->getRunningProcesses();
        self::assertEquals(['/bin/ps -ww -C ffmpeg -o pid=,args='], $running);
    }

    /**
     * Test retrieval of the broadcast id.
     */
    public function testGetBroadcastId(): void
    {
        $command = $this->getSchedulerCommands();
        // @codingStandardsIgnoreLine
        $broadcastId = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata broadcast_id=1337');
        self::assertEquals(1337, $broadcastId);

        self::assertNull($command->getBroadcastId(''));

        // @codingStandardsIgnoreLine
        $broadcastId = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata');
        self::assertNull($broadcastId);

        // @codingStandardsIgnoreLine
        $broadcastId = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata broadcast_id=');
        self::assertNull($broadcastId);
    }

    /**
     * Test retrieval of the process id.
     */
    public function testGetProcessId(): void
    {
        $command = $this->getSchedulerCommands();
        self::assertEquals(0, $command->getProcessId(''));

        // @codingStandardsIgnoreLine
        $processId = $command->getProcessId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata broadcast_id=1337');
        self::assertEquals(1234, $processId);

        // @codingStandardsIgnoreLine
        $processId = $command->getProcessId('  5678 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata broadcast_id=1337');
        self::assertEquals(5678, $processId);

        self::assertNull($command->getBroadcastId('test 5678'));
    }

    /**
     * Test retrieval of the channel id.
     */
    public function testGetChannelId(): void
    {
        $command = $this->getSchedulerCommands();
        self::assertNull($command->getChannelId(''));
        self::assertNull($command->getChannelId('channel_id=12'));

        // @codingStandardsIgnoreLine
        $channelId = $command->getChannelId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata broadcast_id=1337 -metadata channel_id=5');
        self::assertEquals(5, $channelId);
    }

    /**
     * Test the getEnvironment function
     */
    public function testGetEnvironment(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('getProjectDir')->willReturn('/some/directory');
        $kernel->expects(self::once())->method('getEnvironment')->willReturn('unit_test');

        $command = new SchedulerCommands($kernel);
        self::assertNull($command->getEnvironment(''));

        // @codingStandardsIgnoreLine
        $env = $command->getEnvironment('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=prod -metadata broadcast_id=1337 -metadata channel_id=5');
        self::assertEquals('prod', $env);

        // @codingStandardsIgnoreLine
        $env = $command->getEnvironment('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env= -metadata broadcast_id=1337');
        self::assertEquals('', $env);

        // @codingStandardsIgnoreLine
        $env = $command->getEnvironment('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata broadcast_id=1337');
        self::assertNull($env);
    }

    /**
     * @return SchedulerCommands
     */
    protected function getSchedulerCommands(): SchedulerCommands
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('getProjectDir')->willReturn('/some/directory');
        $kernel->expects(self::once())->method('getEnvironment')->willReturn('unit_test');

        return new SchedulerCommands($kernel, true);
    }
}
