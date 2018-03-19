<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractSchedulerCommandsTest
 */
class AbstractSchedulerCommandsTest extends TestCase
{
    /**
     * @var AbstractSchedulerCommands|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulerCommands;

    /**
     * Setup a basic test object
     */
    public function setUp()
    {
        $arguments = ['/some/directory', 'test'];
        $this->schedulerCommands = $this->getMockForAbstractClass(AbstractSchedulerCommands::class, $arguments);
    }

    /**
     * Test stopping the process throws exception
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testStopProcess()
    {
        $this->schedulerCommands->stopProcess(5);
    }

    /**
     * Test getting the running process throws an exception
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetRunningProcesses()
    {
        $this->schedulerCommands->getRunningProcesses();
    }

    /**
     * Test the FFMPEG log directory setter
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

    /**
     * Test a stream can be loopable
     */
    public function testLoopable()
    {
        $this->schedulerCommands->setIsLoopable(true);

        self::assertTrue($this->schedulerCommands->isLoopable());
    }
}
