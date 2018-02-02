<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\Windows\SchedulerCommands as WindowsCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Darwin\SchedulerCommands as MacCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands as LinuxCommands;

/**
 * Class SchedulerCommandsDetector
 * @package Martin1982\LiveBroadcastBundle\Broadcaster
 */
class SchedulerCommandsDetector
{
    /**
     * Create the class required for scheduler commands.
     *
     * @param string $rootDir
     * @param string $environment
     * @param string $ffmpegLogDirectory
     *
     * @return SchedulerCommandsInterface
     */
    public static function createSchedulerCommands($rootDir, $environment, $ffmpegLogDirectory)
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
