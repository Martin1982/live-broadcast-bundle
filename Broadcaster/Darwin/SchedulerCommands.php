<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster\Darwin;

use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands as LinuxCommands;

/**
 * Class SchedulerCommands
 * @package Martin1982\LiveBroadcastBundle\Broadcaster\Darwin
 */
class SchedulerCommands extends LinuxCommands
{
    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses()
    {
        exec('ps -ww -o pid=,args= | grep ffmpeg | grep -v grep', $output);

        return $output;
    }
}
