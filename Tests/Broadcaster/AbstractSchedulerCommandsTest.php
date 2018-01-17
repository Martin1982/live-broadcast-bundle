<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractSchedulerCommandsTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Broadcaster
 */
class AbstractSchedulerCommandsTest extends TestCase
{
    /**
     * @var AbstractSchedulerCommands|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulerCommands;

    /**
     *
     */
    public function setUp()
    {
        $this->schedulerCommands = $this->getMockForAbstractClass(AbstractSchedulerCommands::class, ['test']);
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testStopProcess()
    {
        $this->schedulerCommands->stopProcess(5);
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetRunningProcesses()
    {
        $this->schedulerCommands->getRunningProcesses();
    }

    /**
     *
     */
    public function testMonitorStream()
    {
        self::assertFalse(
            $this->schedulerCommands->isMonitorStream(
                '1234 ffmpeg -re -i -metadata env=prod -metadata broadcast_id=1337'
            )
        );

        self::assertFalse(
            $this->schedulerCommands->isMonitorStream(
                '1234 ffmpeg -re -i -metadata env=prod -metadata monitor_stream=no'
            )
        );

        self::assertTrue(
            $this->schedulerCommands->isMonitorStream(
                '1234 ffmpeg -re -i -metadata env=prod -metadata monitor_stream=yes'
            )
        );
    }

    /**
     *
     */
    public function testFFMpegLogDirectory()
    {
        $this->schedulerCommands->setFFMpegLogDirectory(__DIR__);
        $this->schedulerCommands->setFFMpegLogDirectory('/does/not/exist');

        $reflection = new \ReflectionClass($this->schedulerCommands);
        $property = $reflection->getProperty('logDirectoryFFMpeg');
        $property->setAccessible(true);

        // Second setFFMpegLogDirectory() should be ignored
        self::assertEquals(__DIR__, $property->getValue($this->schedulerCommands));
    }
}
