<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsDetector;
use Martin1982\LiveBroadcastBundle\Broadcaster\Windows\SchedulerCommands as WindowsCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Darwin\SchedulerCommands as DarwinCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands as LinuxCommands;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SchedulerCommandsDetectorTest
 */
class SchedulerCommandsDetectorTest extends TestCase
{
    /**
     * Test scheduler command class detector
     * @throws Exception
     */
    public function testCreateSchedulerCommands(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::atLeastOnce())->method('getProjectDir')->willReturn('.');
        $kernel->expects(self::atLeastOnce())->method('getEnvironment')->willReturn('unit_test');

        $commands = SchedulerCommandsDetector::createSchedulerCommands($kernel, '.', 'Windows XP');
        static::assertInstanceOf(WindowsCommands::class, $commands);
        $commands = SchedulerCommandsDetector::createSchedulerCommands($kernel, '.', 'Darwin 101.1');
        static::assertInstanceOf(DarwinCommands::class, $commands);
        $commands = SchedulerCommandsDetector::createSchedulerCommands($kernel, '.', 'Linux Kernel 2052');
        static::assertInstanceOf(LinuxCommands::class, $commands);
    }
}
