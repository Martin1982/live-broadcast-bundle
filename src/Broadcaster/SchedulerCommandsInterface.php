<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

/**
 * Interface SchedulerCommandsInterface
 */
interface SchedulerCommandsInterface
{
    /**
     * @param string $input
     * @param string $output
     * @param array  $metadata
     *
     * @return string
     */
    public function startProcess(string $input, string $output, array $metadata): string;

    /**
     * @param int $pid
     *
     * @return string
     */
    public function stopProcess(int $pid): string;

    /**
     * @return array
     */
    public function getRunningProcesses(): array;

    /**
     * @param string $processString
     *
     * @return int|null
     */
    public function getProcessId(string $processString): ?int;

    /**
     * @param string $processString
     *
     * @return int|null
     */
    public function getBroadcastId(string $processString): ?int;

    /**
     * @param string $processString
     *
     * @return int|null
     */
    public function getChannelId(string $processString): ?int;

    /**
     * @param string $processString
     *
     * @return string|null
     */
    public function getEnvironment(string $processString): ?string;

    /**
     * @return string|null
     */
    public function getKernelEnvironment(): ?string;

    /**
     * @param string $directory
     */
    public function setFFMpegLogDirectory(string $directory);

    /**
     * @param bool $looping
     */
    public function setLooping(bool $looping);
}
