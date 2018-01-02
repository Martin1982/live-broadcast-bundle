<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster\Linux;

use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands;
use phpmock\phpunit\PHPMock;

/**
 * Class SchedulerCommandsLinuxTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Broadcaster\Linux
 */
class SchedulerCommandsLinuxTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

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
                self::assertEquals('/bin/ps -ww -C ffmpeg -o pid=,args=', $command);
                // @codingStandardsIgnoreLine
                $output = '1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337';
            }
        );

        $running = $command->getRunningProcesses();
        // @codingStandardsIgnoreLine
        self::assertEquals('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337', $running);
    }
}
