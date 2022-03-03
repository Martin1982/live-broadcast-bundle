<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\Windows\SchedulerCommands as WindowsCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Darwin\SchedulerCommands as MacCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands as LinuxCommands;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SchedulerCommandsDetector
 */
class SchedulerCommandsDetector
{
    /**
     * Create the class required for scheduler commands.
     *
     * @param Kernel      $kernel
     * @param string|null $ffmpegLogDirectory
     *
     * @return SchedulerCommandsInterface
     */
    public static function createSchedulerCommands(Kernel $kernel, ?string $ffmpegLogDirectory = null): SchedulerCommandsInterface
    {
        $osCode = strtoupper(substr(PHP_OS, 0, 3));

        switch ($osCode) {
            case 'WIN':
                $schedulerCommands = new WindowsCommands($kernel);
                break;
            case 'DAR':
                $schedulerCommands = new MacCommands($kernel);
                break;
            default:
                $schedulerCommands = new LinuxCommands($kernel);
                break;
        }

        $schedulerCommands->setFFMpegLogDirectory($ffmpegLogDirectory);

        return $schedulerCommands;
    }
}
