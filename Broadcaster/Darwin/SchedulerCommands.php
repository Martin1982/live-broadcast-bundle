<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Broadcaster\Darwin;

use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands as LinuxCommands;

/**
 * Class SchedulerCommands
 */
class SchedulerCommands extends LinuxCommands
{
    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses(): array
    {
        $output = $this->exec('ps -ww -o pid=,args= | grep ffmpeg | grep -v grep', true);

        if (!is_array($output)) {
            $output = [$output];
        }

        return $output;
    }
}
