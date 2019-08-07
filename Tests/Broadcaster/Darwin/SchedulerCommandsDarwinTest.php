<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster\Darwin;

use Martin1982\LiveBroadcastBundle\Broadcaster\Darwin\SchedulerCommands;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SchedulerCommandsDarwinTest
 */
class SchedulerCommandsDarwinTest extends TestCase
{
    use PHPMock;

    /**
     * Test the running processes command.
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetRunningProcesses(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('getProjectDir')->willReturn('/some/directory');
        $kernel->expects(self::once())->method('getEnvironment')->willReturn('unit_test');

        $command = new SchedulerCommands($kernel);

        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster\Darwin', 'exec');
        $exec->expects(static::once())->willReturnCallback(
            static function ($command, &$output) {
                self::assertEquals('ps -ww -o pid=,args= | grep ffmpeg | grep -v grep', $command);
                // @codingStandardsIgnoreLine
                $output[] = '1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata broadcast_id=1337';
            }
        );

        $running = $command->getRunningProcesses();
        // @codingStandardsIgnoreLine
        self::assertEquals('1234 ffmpeg -re -i /path/to/video.mp4 -vcodec copy -acodec copy -f flv rtmp://live-ams.twitch.tv/app/ -metadata env=unit_test -metadata broadcast_id=1337', $running[0]);
    }
}
