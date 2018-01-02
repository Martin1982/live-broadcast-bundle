<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster\Darwin;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;

/**
 * Class SchedulerCommands
 * @package Martin1982\LiveBroadcastBundle\Broadcaster\Darwin
 */
class SchedulerCommands extends AbstractSchedulerCommands
{
    /**
     * {@inheritdoc}
     */
    public function stopProcess($pid)
    {
        return exec(sprintf('kill %d', $pid));
    }

    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses()
    {
        exec('ps -ww -o pid=,args= | grep ffmpeg | grep -v grep', $output);

        return $output;
    }
}
