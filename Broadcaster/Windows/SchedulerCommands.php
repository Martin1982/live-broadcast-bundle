<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster\Windows;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;

/**
 * Class SchedulerCommands
 * @package Martin1982\LiveBroadcastBundle\Broadcaster\Windows
 */
class SchedulerCommands extends AbstractSchedulerCommands
{
    /**
     * {@inheritdoc}
     */
    public function stopProcess($pid)
    {
        return exec(sprintf('TASKKILL /PID %d /T', $pid));
    }

    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses()
    {
        exec('TASKLIST /FI "IMAGENAME eq ffmpeg.exe" /FO CSV', $output);

        return $output;
    }

    /**
     * Execute the shell command to start the stream
     *
     * @param $input
     * @param $output
     * @param $meta
     *
     * @return string
     */
    protected function execStreamCommand($input, $output, $meta)
    {
        return exec(sprintf('ffmpeg %s %s%s >nul 2>nul &', $input, $output, $meta));
    }
}
