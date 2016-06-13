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
     * Create the class required for scheduler commands
     * @param string $environment
     * @return SchedulerCommandsInterface
     */
    public static function createSchedulerCommands($environment)
    {
        $osCode = strtoupper(substr(PHP_OS, 0, 3));

        switch ($osCode) {
            case 'WIN':
                return new WindowsCommands($environment);
                break;
            case 'DAR':
                return new MacCommands($environment);
                break;
            default:
                return new LinuxCommands($environment);
                break;
        }
    }
}
