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
    public function startProcess($input, $output, $metadata): string;

    /**
     * @param int $pid
     *
     * @return string
     */
    public function stopProcess($pid): string;

    /**
     * @return array
     */
    public function getRunningProcesses(): array;

    /**
     * @param string $processString
     *
     * @return int|null
     */
    public function getProcessId($processString): ?int;

    /**
     * @param string $processString
     *
     * @return int|null
     */
    public function getBroadcastId($processString): ?int;

    /**
     * @param string $processString
     *
     * @return int|null
     */
    public function getChannelId($processString): ?int;

    /**
     * @param string $processString
     *
     * @return string|null
     */
    public function getEnvironment($processString): ?string;

    /**
     * @return string|null
     */
    public function getKernelEnvironment(): ?string;

    /**
     * @param string $directory
     */
    public function setFFMpegLogDirectory($directory);

    /**
     * @param bool $loopable
     */
    public function setLoopable($loopable);
}
