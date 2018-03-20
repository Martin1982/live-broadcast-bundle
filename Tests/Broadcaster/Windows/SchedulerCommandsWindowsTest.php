<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster\Windows;

use Martin1982\LiveBroadcastBundle\Broadcaster\Windows\SchedulerCommands;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * Class SchedulerCommandsWindowsTest
 */
class SchedulerCommandsWindowsTest extends TestCase
{
    use PHPMock;

    /**
     * Test the stop process command.
     */
    public function testStopProcess(): void
    {
        $command = new SchedulerCommands('/some/directory', 'unittest');

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Windows', 'exec');
        $exec->expects(static::once())->willReturnCallback(
            // phpcs:disable Symfony.Functions.ReturnType.Invalid
            function ($command) {
                self::assertEquals('TASKKILL /PID 1337 /T', $command);

                return 'killed';
            }
            // phpcs:enable
        );

        $command->stopProcess(1337);
    }

    /**
     * Test the running processes command.
     */
    public function testGetRunningProcesses(): void
    {
        $command = new SchedulerCommands('/some/directory', 'unittest');

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Windows', 'exec');
        $exec->expects(static::once())->willReturnCallback(
            function ($command, &$output) {
                self::assertEquals('TASKLIST /FI "IMAGENAME eq ffmpeg.exe" /FO CSV', $command);
                // @codingStandardsIgnoreLine
                $output[] = '1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337';
            }
        );

        $running = $command->getRunningProcesses();
        // @codingStandardsIgnoreLine
        self::assertEquals('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unittest -metadata broadcast_id=1337', $running[0]);
    }

    /**
     * Test running the stream command
     */
    public function testExecStreamCommand(): void
    {
        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Windows', 'exec');
        $exec->expects(static::once())
            // @codingStandardsIgnoreLine
            ->with('ffmpeg -stream_loop -1 input output -metadata x=y -metadata a=b -metadata env=unittest >nul 2>nul &')
            ->willReturn('Streaming...');

        $command = new SchedulerCommands('/some/directory', 'unittest');
        $command->setIsLoopable(true);
        $command->startProcess('input', 'output', [ 'x' => 'y', 'a' => 'b']);
    }
}
