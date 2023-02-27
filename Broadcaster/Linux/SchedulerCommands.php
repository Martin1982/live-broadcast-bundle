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
    public function stopProcess(int $pid): string
    {
        return $this->exec(sprintf('kill %d', $pid));
    }

    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses(): array
    {
        $output = $this->exec('/bin/ps -ww -C ffmpeg -o pid=,args=', true);

        if (!is_array($output)) {
            $output = [$output];
        }

        return $output;
    }
}
