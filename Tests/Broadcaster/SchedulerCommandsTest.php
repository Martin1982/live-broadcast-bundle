<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommands;
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

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster', "exec");
        $exec->expects($this->once())->willReturnCallback(
            function ($command) {
                $this->assertEquals("ffmpeg input output -metadata broadcast_id=4 -metadata unit=test -metadata env=unittest >/dev/null 2>&1 &", $command);
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

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster', "exec");
        $exec->expects($this->once())->willReturnCallback(
            function ($command) {
                $this->assertEquals("kill 1337", $command);
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

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster', "exec");
        $exec->expects($this->once())->willReturnCallback(
            function ($command, &$output) {
                $this->assertEquals("/bin/ps -C ffmpeg -o pid=,args=", $command);
                $output = '1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337';
            }
        );

        $running = $command->getRunningProcesses();
        $this->assertEquals($running, '1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337');
    }

    /**
     * Test retrieval of the broadcast id.
     */
    public function testGetBroadcastId()
    {
        $command = new SchedulerCommands('unittest');
        $id = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337');
        $this->assertEquals($id, 1337);

        $id = $command->getBroadcastId('');
        $this->assertEquals($id, null);

        $id = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata');
        $this->assertEquals($id, null);

        $id = $command->getBroadcastId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=');
        $this->assertEquals($id, null);
    }

    /**
     * Test retrieval of the process id.
     */
    public function testGetProcessId()
    {
        $command = new SchedulerCommands('unittest');
        $id = $command->getProcessId('');
        $this->assertEquals($id, null);

        $id = $command->getProcessId('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337');
        $this->assertEquals($id, 1234);

        $id = $command->getBroadcastId('test 5678');
        $this->assertEquals($id, null);
    }
}