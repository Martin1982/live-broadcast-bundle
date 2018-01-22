<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AbstractSchedulerCommands
 * @package Martin1982\LiveBroadcastBundle\Broadcaster
 */
abstract class AbstractSchedulerCommands implements SchedulerCommandsInterface
{
    const METADATA_BROADCAST = 'broadcast_id';
    const METADATA_CHANNEL = 'channel_id';
    const METADATA_ENVIRONMENT = 'env';
    const METADATA_MONITOR = 'monitor_stream';
    const LOG_FILE = 'livebroadcaster-ffmpeg-%s.log';

    /**
     * Symfony kernel environment name
     *
     * @var string
     */
    protected $kernelEnvironment;

    /**
     * Directory to store FFMpeg logs
     *
     * @var string
     */
    protected $logDirectoryFFMpeg = '';

    /**
     * @var bool Can the input be looped
     */
    protected $loopable = false;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * SchedulerCommands constructor.
     *
     * @param string $rootDir
     * @param string $kernelEnvironment
     */
    public function __construct($rootDir, $kernelEnvironment)
    {
        $this->rootDir = $rootDir;
        $this->kernelEnvironment = $kernelEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function startProcess($input, $output, $metadata)
    {
        $meta = '';
        $metadata['env'] = $this->getKernelEnvironment();

        foreach ($metadata as $key => $value) {
            $meta .= ' -metadata '.$key.'='.$value;
        }

        $this->execStreamCommand($input, $output, $meta);
    }

    /**
     * {@inheritdoc}
     * @throws LiveBroadcastException
     */
    public function stopProcess($pid)
    {
        throw new LiveBroadcastException('stopProcess Cannot be called on the abstract class');
    }

    /**
     * {@inheritdoc}
     * @throws LiveBroadcastException
     */
    public function getRunningProcesses()
    {
        throw new LiveBroadcastException('getRunningProcesses Cannot be called on the abstract class');
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessId($processString)
    {
        preg_match('/^\s*([\d]+)/', $processString, $pid);
        if (count($pid) && is_numeric($pid[0])) {
            return (int) $pid[0];
        }

        return 0;
    }

    /**
     * Get the process id for a given stream
     *
     * @param int $broadcastId
     * @param int $channelId
     *
     * @return string|null
     *
     * @throws LiveBroadcastException
     */
    public function getProcessIdForStream($broadcastId, $channelId)
    {
        $processes = $this->getRunningProcesses();

        foreach ($processes as $process) {
            $runningBroadcastId = $this->getBroadcastId($process);
            $runningChannelId = $this->getChannelId($process);

            $isBroadcast = (string) $runningBroadcastId === (string) $broadcastId;
            $isChannel = (string) $runningChannelId === (string) $channelId;

            if ($isBroadcast && $isChannel) {
                return $this->getProcessId($process);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isMonitorStream($processString)
    {
        return ($this->getMetadataValue($processString, self::METADATA_MONITOR) === 'yes');
    }

    /**
     * {@inheritdoc}
     */
    public function getBroadcastId($processString)
    {
        return $this->getMetadataValue($processString, self::METADATA_BROADCAST);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelId($processString)
    {
        return $this->getMetadataValue($processString, self::METADATA_CHANNEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment($processString)
    {
        return $this->getMetadataValue($processString, self::METADATA_ENVIRONMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getKernelEnvironment()
    {
        return $this->kernelEnvironment;
    }

    /**
     * Execute the command to start the stream
     *
     * @param string $input
     * @param string $output
     * @param string $meta
     *
     * @return string
     */
    protected function execStreamCommand($input, $output, $meta)
    {
        $logFile = '/dev/null';
        $loop = '';

        if (!empty($this->logDirectoryFFMpeg)) {
            $now = new \DateTime();
            $logFile = $this->logDirectoryFFMpeg.DIRECTORY_SEPARATOR.sprintf(self::LOG_FILE, $now->format('Y-m-d_His'));
        }

        if ($this->isLoopable()) {
            $loop = '-stream_loop -1 ';
        }

        $streamStart = sprintf('ffmpeg %s%s %s%s >%s 2>&1;', $loop, $input, $output, $meta, $logFile);
        $streamEndCommand = '';

        $broadcastId = $this->getBroadcastId($streamStart);
        $channelId = $this->getChannelId($streamStart);

        $consolePath = Kernel::VERSION_ID > 30000 ? 'bin/console' : 'app/console';
        $consolePath = $this->rootDir.'/../'.$consolePath;

        if (file_exists($consolePath)) {
            $streamEndCommand = sprintf('%s livebroadcaster:broadcast:end %s %s', $consolePath, $broadcastId, $channelId);
        }

        return exec('('.$streamStart.$streamEndCommand.') &');
    }

    /**
     * Read metadata from a process string.
     *
     * @param string $processString
     *
     * @return array
     */
    protected function readMetadata($processString)
    {
        $processMetadata = [];
        $metadataRegex = '/-metadata ([a-z_]+)=([[:alnum:]]+)/';
        preg_match_all($metadataRegex, $processString, $metadata);

        if (count($metadata) === 3 && is_array($metadata[1]) && is_array($metadata[2])) {
            foreach ($metadata[1] as $metadataIndex => $metadataKey) {
                $processMetadata[$metadataKey] = $metadata[2][$metadataIndex];
            }
        }

        return $processMetadata;
    }

    /**
     * @param string $processString
     * @param string $metadataKey
     *
     * @return mixed
     */
    private function getMetadataValue($processString, $metadataKey)
    {
        $metadata = $this->readMetadata($processString);
        $value = null;

        if (array_key_exists($metadataKey, $metadata)) {
            $value = $metadata[$metadataKey];
        }

        return $value;
    }

    /**
     * @param string $directory
     */
    public function setFFMpegLogDirectory($directory)
    {
        if (!is_writable($directory)) {
            return;
        }

        $this->logDirectoryFFMpeg = $directory;
    }

    /**
     * @param boolean $loopable
     */
    public function setIsLoopable($loopable)
    {
        $this->loopable = (boolean) $loopable;
    }

    /**
     * @return bool
     */
    public function isLoopable()
    {
        return $this->loopable;
    }
}
