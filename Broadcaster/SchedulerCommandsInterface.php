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
    public function startProcess($input, $output, $metadata);

    /**
     * @param int $pid
     *
     * @return string
     */
    public function stopProcess($pid);

    /**
     * @return array
     */
    public function getRunningProcesses();

    /**
     * @param string $processString
     *
     * @return int|null
     */
    public function getProcessId($processString);

    /**
     * @param string $processString
     *
     * @return bool
     */
    public function isMonitorStream($processString);

    /**
     * @param string $processString
     *
     * @return int|null
     */
    public function getBroadcastId($processString);

    /**
     * @param string $processString
     *
     * @return int|null
     */
    public function getChannelId($processString);

    /**
     * @param string $processString
     *
     * @return string|null
     */
    public function getEnvironment($processString);

    /**
     * @return string
     */
    public function getKernelEnvironment();

    /**
     * @param string $directory
     */
    public function setFFMpegLogDirectory($directory);

    /**
     * @param bool $loopable
     */
    public function setIsLoopable($loopable);
}
