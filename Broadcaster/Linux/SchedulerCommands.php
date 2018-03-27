<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Broadcaster\Linux;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;

/**
 * Class SchedulerCommands
 */
class SchedulerCommands extends AbstractSchedulerCommands
{
    /**
     * {@inheritdoc}
     */
    public function stopProcess($pid): string
    {
        return exec(sprintf('kill %d', $pid));
    }

    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses(): array
    {
        exec('/bin/ps -ww -C ffmpeg -o pid=,args=', $output);

        return $output;
    }
}
