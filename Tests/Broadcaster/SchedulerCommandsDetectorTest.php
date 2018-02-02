<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsDetector;
use Martin1982\LiveBroadcastBundle\Broadcaster\Windows\SchedulerCommands as WindowsCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Darwin\SchedulerCommands as DarwinCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands as LinuxCommands;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * Class SchedulerCommandsDetectorTest
 */
class SchedulerCommandsDetectorTest extends TestCase
{
    use PHPMock;

    /**
     * Test scheduler command class detector
     */
    public function testCreateSchedulerCommands()
    {
        $strtoupper = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Broadcaster', 'strtoupper');
        $strtoupper->expects($this->any())
            ->willReturnOnConsecutiveCalls('WIN', 'DAR', 'LIN');

        $this->assertInstanceOf(WindowsCommands::class, SchedulerCommandsDetector::createSchedulerCommands('.', 'test', '.'));
        $this->assertInstanceOf(DarwinCommands::class, SchedulerCommandsDetector::createSchedulerCommands('.', 'test', '.'));
        $this->assertInstanceOf(LinuxCommands::class, SchedulerCommandsDetector::createSchedulerCommands('.', 'test', '.'));
    }
}
