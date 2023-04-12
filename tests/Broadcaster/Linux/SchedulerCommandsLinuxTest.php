<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster\Linux;

use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SchedulerCommandsLinuxTest
 */
class SchedulerCommandsLinuxTest extends TestCase
{
    /**
     * Test the stop process command.
     *
     * @throws LiveBroadcastException
     * @throws Exception
     */
    public function testStopProcess(): void
    {
        $command = new SchedulerCommands($this->getKernel(), true);
        $output = $command->stopProcess(1337);
        self::assertEquals('kill 1337', $output);
    }

    /**
     * Test the running processes command.
     *
     * @throws LiveBroadcastException
     * @throws Exception
     */
    public function testGetRunningProcesses(): void
    {
        $command = new SchedulerCommands($this->getKernel(), true);
        $running = $command->getRunningProcesses();
        self::assertEquals(['/bin/ps -ww -C ffmpeg -o pid=,args='], $running);
    }

    /**
     * Test running the stream command
     *
     * @throws \Exception
     * @throws Exception
     */
    public function testExecStreamCommand(): void
    {
        $command = new SchedulerCommands($this->getKernel(), true);
        $command->setLooping(true);
        $output = $command->startProcess('input', 'output', [ 'x' => 'y', 'a' => 'b']);
        self::assertEquals('ffmpeg -stream_loop -1 input output -metadata x=y -metadata a=b -metadata env=unit_test >> /dev/null 2>&1 &', $output);
    }

    /**
     * Get the application kernel mock
     *
     * @return Kernel
     * @throws Exception
     */
    protected function getKernel(): Kernel
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(self::once())->method('getProjectDir')->willReturn('/some/directory');
        $kernel->expects(self::once())->method('getEnvironment')->willReturn('unit_test');

        return $kernel;
    }
}
