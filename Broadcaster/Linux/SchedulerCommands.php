<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster\Linux;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;

/**
 * Class SchedulerCommands.
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
        exec('/bin/ps -C ffmpeg -o pid=,args=', $output);

        return $output;
    }
}
