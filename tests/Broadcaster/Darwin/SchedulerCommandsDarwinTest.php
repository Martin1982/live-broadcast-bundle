<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster\Darwin;

use Martin1982\LiveBroadcastBundle\Broadcaster\Darwin\SchedulerCommands;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SchedulerCommandsDarwinTest
 */
class SchedulerCommandsDarwinTest extends TestCase
{
    /**
     * Test the running processes command.
     *
     * @throws LiveBroadcastException
     * @throws Exception
     */
    public function testGetRunningProcesses(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('getProjectDir')->willReturn('/some/directory');
        $kernel->expects(self::once())->method('getEnvironment')->willReturn('unit_test');

        $command = new SchedulerCommands($kernel, true);

        $running = $command->getRunningProcesses();
        self::assertEquals(['ps -ww -o pid=,args= | grep ffmpeg | grep -v grep'], $running);
    }
}
