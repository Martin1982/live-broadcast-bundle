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
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SchedulerCommandsWindowsTest
 */
class SchedulerCommandsWindowsTest extends TestCase
{
    use PHPMock;

    /**
     * Test the stop process command.
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testStopProcess(): void
    {
        $command = new SchedulerCommands($this->getKernel());

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Windows', 'exec');
        $exec->expects(static::once())->willReturnCallback(
            // phpcs:disable Symfony.Functions.ReturnType.Invalid
            static function ($command) {
                self::assertEquals('START /B TASKKILL /PID 1337 /T', $command);

                return 'killed';
            }
            // phpcs:enable
        );

        $command->stopProcess(1337);
    }

    /**
     * Test the running processes command.
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetRunningProcesses(): void
    {
        $command = new SchedulerCommands($this->getKernel());

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Windows', 'exec');
        $exec->expects(static::once())->willReturnCallback(
            static function ($command, &$output) {
                self::assertEquals('START /B TASKLIST /FI "IMAGENAME eq ffmpeg.exe" /FO CSV', $command);
                // @codingStandardsIgnoreLine
                $output[] = '1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata broadcast_id=1337';
            }
        );

        $running = $command->getRunningProcesses();
        // @codingStandardsIgnoreLine
        self::assertEquals('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata broadcast_id=1337', $running[0]);
    }

    /**
     * Test running the stream command
     * @throws \Exception
     */
    public function testExecStreamCommand(): void
    {
        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Windows', 'exec');
        $exec->expects(static::once())
            // @codingStandardsIgnoreLine
            ->with('START /B ffmpeg -stream_loop -1 input output -metadata x=y -metadata a=b -metadata env=unit_test >nul 2>nul &')
            ->willReturn('Streaming...');

        $command = new SchedulerCommands($this->getKernel());
        $command->setLooping(true);
        $command->startProcess('input', 'output', [ 'x' => 'y', 'a' => 'b']);
    }

    /**
     * Get a KernelInterface mock object
     *
     * @return Kernel
     */
    protected function getKernel(): Kernel
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('getProjectDir')->willReturn('/some/directory');
        $kernel->expects(self::once())->method('getEnvironment')->willReturn('unit_test');

        return $kernel;
    }
}
