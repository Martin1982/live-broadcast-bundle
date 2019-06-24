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

/**
 * Class SchedulerCommandsDetector
 */
class SchedulerCommandsDetector
{
    /**
     * Create the class required for scheduler commands.
     *
     * @param string      $rootDir
     * @param string      $environment
     * @param string|null $ffmpegLogDirectory
     *
     * @return SchedulerCommandsInterface
     */
    public static function createSchedulerCommands($rootDir, $environment, $ffmpegLogDirectory = null): SchedulerCommandsInterface
    {
        $osCode = strtoupper(substr(PHP_OS, 0, 3));

        switch ($osCode) {
            case 'WIN':
                $schedulerCommands = new WindowsCommands($rootDir, $environment);
                break;
            case 'DAR':
                $schedulerCommands = new MacCommands($rootDir, $environment);
                break;
            default:
                $schedulerCommands = new LinuxCommands($rootDir, $environment);
                break;
        }

        $schedulerCommands->setFFMpegLogDirectory($ffmpegLogDirectory);

        return $schedulerCommands;
    }
}
