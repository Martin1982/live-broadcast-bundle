<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * Class SchedulerCommandsTest
 */
class SchedulerCommandsTest extends TestCase
{
    use PHPMock;

    /**
     * Test the start process command.
     */
    public function testStartProcess(): void
    {
        $command = $this->getSchedulerCommands();

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster', 'exec');
        $exec->expects(static::once())->willReturnCallback(
            // phpcs:disable Symfony.Functions.ReturnType.Invalid
            function ($command) {
                // @codingStandardsIgnoreLine
                self::assertEquals('ffmpeg input output -metadata broadcast_id=4 -metadata unit=test -metadata env=unittest >> /dev/null 2>&1 &', $command);

                return '';
            }
            // phpcs:enable Symfony.Functions.ReturnType.Invalid
        );

        $command->startProcess('input', 'output', ['broadcast_id' => 4, 'unit' => 'test']);
    }

    /**
     * Test the start process command with a log directory configured
     */
    public function testStartProcessWithLogDirectory(): void
    {
        $command = $this->getSchedulerCommands();
        $command->setFFMpegLogDirectory('/tmp');

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster', 'exec');
        $exec->expects(static::once())->willReturnCallback(
            // phpcs:disable Symfony.Functions.ReturnType.Invalid
            function ($command) {
                $now = new \DateTime();
                // @codingStandardsIgnoreLine
                self::assertEquals('ffmpeg input output -metadata broadcast_id=12 -metadata test=unit -metadata env=unittest >> /tmp/livebroadcaster-ffmpeg-'.$now->format('Y-m-d_His').'.log 2>&1 &', $command);

                return '';
            }
            // phpcs:enable Symfony.Functions.ReturnType.Invalid
        );

        $command->startProcess('input', 'output', ['broadcast_id' => 12, 'test' => 'unit']);
    }

    /**
     * Test the stop process command.
     */
    public function testStopProcess(): void
    {
        $command = $this->getSchedulerCommands();

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Linux', 'exec');
        $exec->expects(static::once())->willReturnCallback(
            // phpcs:disable Symfony.Functions.ReturnType.Invalid
            function ($command) {
                self::assertEquals('kill 1337', $command);

                return '';
            }
            // phpcs:enable Symfony.Functions.ReturnType.Invalid
        );

        $command->stopProcess(1337);
    }

    /**
     * Test the running processes command.
     */
    public function testGetRunningProcesses(): void
    {
        $command = $this->getSchedulerCommands();

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Linux', 'exec');
        $exec->expects(static::once())->willReturnCallback(
            function ($command, &$output) {
                self::assertEquals('/bin/ps -ww -C ffmpeg -o pid=,args=', $command);
                // @codingStandardsIgnoreLine
                $output[] = '1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337';
            }
        );

        $running = $command->getRunningProcesses();
        // @codingStandardsIgnoreLine
        self::assertEquals('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337', $running[0]);
    }

    /**
     * Test retrieval of the broadcast id.
     */
    public function testGetBroadcastId(): void
    {
        $command = $this->getSchedulerCommands();
        // @codingStandardsIgnoreLine
        $broadcastId = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337');
        self::assertEquals(1337, $broadcastId);

        self::assertNull($command->getBroadcastId(''));

        // @codingStandardsIgnoreLine
        $broadcastId = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata');
        self::assertNull($broadcastId);

        // @codingStandardsIgnoreLine
        $broadcastId = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=');
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
        $processId = $command->getProcessId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337');
        self::assertEquals(1234, $processId);

        // @codingStandardsIgnoreLine
        $processId = $command->getProcessId('  5678 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337');
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
        self::assertNull($command->getChannelId('channelid=12'));

        // @codingStandardsIgnoreLine
        $channelId = $command->getChannelId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337 -metadata channel_id=5');
        self::assertEquals(5, $channelId);
    }

    /**
     * Test the getEnvironment function
     */
    public function testGetEnvironment(): void
    {
        $command = new SchedulerCommands('/some/directory', '');
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
        return new SchedulerCommands('/some/directory', 'unittest');
    }
}
