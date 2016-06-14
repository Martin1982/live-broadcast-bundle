<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster\Windows;

use Martin1982\LiveBroadcastBundle\Broadcaster\Windows\SchedulerCommands;
use phpmock\phpunit\PHPMock;

/**
 * Class SchedulerCommandsWindowsTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Broadcaster\Windows
 */
class SchedulerCommandsWindowsTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    /**
     * Test the stop process command.
     */
    public function testStopProcess()
    {
        $command = new SchedulerCommands('unittest');

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Windows', "exec");
        $exec->expects($this->once())->willReturnCallback(
            function ($command) {
                self::assertEquals("TASKKILL /PID 1337 /T", $command);
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

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Windows', "exec");
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output) {
                self::assertEquals("TASKLIST /FI \"IMAGENAME eq ffmpeg.exe\" /FO CSV", $command);
                $output = '1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337';
            }
        );

        $running = $command->getRunningProcesses();
        self::assertEquals('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337', $running);
    }
}