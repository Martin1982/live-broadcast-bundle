<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster\Windows;
use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;

/**
 * Class SchedulerCommands
 * @package Martin1982\LiveBroadcastBundle\Broadcaster
 */
class SchedulerCommands extends AbstractSchedulerCommands
{
    /**
     * {@inheritdoc}
     */
    public function stopProcess($pid)
    {
        return exec(sprintf("TASKKILL /PID %d /T", $pid));
    }

    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses()
    {
        exec('TASKLIST /FI "IMAGENAME eq ffmpeg.exe" /FO CSV', $output);

        return $output;
    }
}
