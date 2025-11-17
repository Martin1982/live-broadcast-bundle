<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Broadcaster\Windows;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;

/**
 * Class SchedulerCommands
 */
class SchedulerCommands extends AbstractSchedulerCommands
{
    /**
     * {@inheritdoc}
     */
    public function stopProcess(int $pid): string
    {
        return $this->exec(sprintf('START /B TASKKILL /PID %d /T', $pid));
    }

    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses(): array
    {
        $output = $this->exec('START /B TASKLIST /FI "IMAGENAME eq ffmpeg.exe" /FO CSV', true);

        if (!is_array($output)) {
            $output = [$output];
        }

        return $output;
    }

    /**
     * Execute the shell command to start the stream
     *
     * @param string $input
     * @param string $output
     * @param string $meta
     *
     * @return string
     */
    protected function execStreamCommand(string $input, string $output, string $meta): string
    {
        $loop = '';

        if ($this->isLooping()) {
            $loop = '-stream_loop -1 ';
        }

        return $this->exec(sprintf('START /B ffmpeg %s%s %s%s >nul 2>nul &', $loop, $input, $output, $meta));
    }
}
