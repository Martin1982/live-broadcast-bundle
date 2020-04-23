<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AbstractSchedulerCommandsTest
 */
class AbstractSchedulerCommandsTest extends TestCase
{
    /**
     * @var AbstractSchedulerCommands|MockObject
     */
    private $schedulerCommands;

    /**
     * Setup a basic test object
     */
    public function setUp(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('getProjectDir')->willReturn('/some/directory');
        $kernel->expects(self::once())->method('getEnvironment')->willReturn('unit_test');

        $this->schedulerCommands = $this->getMockForAbstractClass(AbstractSchedulerCommands::class, [$kernel]);
    }

    /**
     * Test stopping the process throws exception
     */
    public function testStopProcess(): void
    {
        $this->expectException(LiveBroadcastException::class);

        $this->schedulerCommands->stopProcess(5);
    }

    /**
     * Test getting the running process throws an exception
     */
    public function testGetRunningProcesses(): void
    {
        $this->expectException(LiveBroadcastException::class);

        $this->schedulerCommands->getRunningProcesses();
    }

    /**
     * Test the FFMPEG log directory setter
     *
     * @throws \ReflectionException
     */
    public function testFFMpegLogDirectory(): void
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
     * Test if a stream can looped
     */
    public function testLooping(): void
    {
        $this->schedulerCommands->setLooping(true);

        self::assertTrue($this->schedulerCommands->isLooping());
    }
}
