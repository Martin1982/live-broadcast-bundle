<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands;
use phpmock\phpunit\PHPMock;

/**
 * Class SchedulerCommandsTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Broadcaster
 */
class SchedulerCommandsTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    /**
     * Test the start process command.
     */
    public function testStartProcess()
    {
        $command = new SchedulerCommands('unittest');

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command) {
                // @codingStandardsIgnoreLine
                self::assertEquals('ffmpeg input output -metadata broadcast_id=4 -metadata unit=test -metadata env=unittest >/dev/null 2>&1 &', $command);
            }
        );

        $command->startProcess('input', 'output', array('broadcast_id' => 4, 'unit' => 'test'));
    }

    /**
     * Test the stop process command.
     */
    public function testStopProcess()
    {
        $command = new SchedulerCommands('unittest');

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Linux', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command) {
                self::assertEquals('kill 1337', $command);
            }
        );

        $command->stopProcess(1337);
    }

    /**
     * Test the running processes command.
     */
    public function testGetRunningProcesses()
    {
        $command = new SchedulerCommands('unittest');

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Linux', 'exec');
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output) {
                self::assertEquals('/bin/ps -C ffmpeg -o pid=,args=', $command);
                // @codingStandardsIgnoreLine
                $output = '1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337';
            }
        );

        $running = $command->getRunningProcesses();
        // @codingStandardsIgnoreLine
        self::assertEquals('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337', $running);
    }

    /**
     * Test retrieval of the broadcast id.
     */
    public function testGetBroadcastId()
    {
        $command = new SchedulerCommands('unittest');
        // @codingStandardsIgnoreLine
        $id = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337');
        self::assertEquals(1337, $id);

        self::assertNull($command->getBroadcastId(''));

        // @codingStandardsIgnoreLine
        $id = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata');
        self::assertNull($id);

        // @codingStandardsIgnoreLine
        $id = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=');
        self::assertNull($id);
    }

    /**
     * Test retrieval of the process id.
     */
    public function testGetProcessId()
    {
        $command = new SchedulerCommands('unittest');
        self::assertEquals(0, $command->getProcessId(''));

        // @codingStandardsIgnoreLine
        $id = $command->getProcessId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337');
        self::assertEquals(1234, $id);

        // @codingStandardsIgnoreLine
        $id = $command->getProcessId('  5678 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337');
        self::assertEquals(5678, $id);

        self::assertNull($command->getBroadcastId('test 5678'));
    }

    /**
     * Test retrieval of the channel id.
     */
    public function testGetChannelId()
    {
        $command = new SchedulerCommands('unittest');
        self::assertNull($command->getChannelId(''));
        self::assertNull($command->getChannelId('channelid=12'));

        // @codingStandardsIgnoreLine
        $id = $command->getChannelId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337 -metadata channel_id=5');
        self::assertEquals(5, $id);
    }

    /**
     * Test the getEnvironment function
     */
    public function testGetEnvironment()
    {
        $command = new SchedulerCommands('');
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
}
