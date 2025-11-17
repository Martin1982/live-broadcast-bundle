<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\Windows\SchedulerCommands as WindowsCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Darwin\SchedulerCommands as MacCommands;
use Martin1982\LiveBroadcastBundle\Broadcaster\Linux\SchedulerCommands as LinuxCommands;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class SchedulerCommandsDetector
 */
class SchedulerCommandsDetector
{
    /**
     * Create the class required for scheduler commands.
     *
     * @param Kernel      $kernel
     * @param string|null $ffmpegLogDirectory
     * @param string|null $os
     *
     * @return SchedulerCommandsInterface
     */
    public static function createSchedulerCommands(Kernel $kernel, ?string $ffmpegLogDirectory = null, ?string $os = null): SchedulerCommandsInterface
    {
        if (null === $os) {
            $os = PHP_OS;
        }

        $osCode = strtoupper(substr($os, 0, 3));

        $schedulerCommands = match ($osCode) {
            'WIN' => new WindowsCommands($kernel),
            'DAR' => new MacCommands($kernel),
            default => new LinuxCommands($kernel),
        };

        $schedulerCommands->setFFMpegLogDirectory($ffmpegLogDirectory);

        return $schedulerCommands;
    }
}
