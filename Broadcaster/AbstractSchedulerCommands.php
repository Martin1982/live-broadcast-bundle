<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class AbstractSchedulerCommands
 */
abstract class AbstractSchedulerCommands implements SchedulerCommandsInterface
{
    public const METADATA_BROADCAST = 'broadcast_id';
    public const METADATA_CHANNEL = 'channel_id';
    public const METADATA_ENVIRONMENT = 'env';
    public const LOG_FILE = 'livebroadcaster-ffmpeg-%s.log';

    /**
     * Symfony kernel environment name
     *
     * @var string|null
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
    public function startProcess($input, $output, $metadata): string
    {
        $meta = '';
        $metadata['env'] = $this->getKernelEnvironment();

        foreach ($metadata as $key => $value) {
            $meta .= ' -metadata '.$key.'='.$value;
        }

        return $this->execStreamCommand($input, $output, $meta);
    }

    /**
     * {@inheritdoc}
     * @throws LiveBroadcastException
     */
    public function stopProcess($pid): string
    {
        throw new LiveBroadcastException('stopProcess Cannot be called on the abstract class');
    }

    /**
     * {@inheritdoc}
     * @throws LiveBroadcastException
     */
    public function getRunningProcesses(): array
    {
        throw new LiveBroadcastException('getRunningProcesses Cannot be called on the abstract class');
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessId($processString): ?int
    {
        preg_match('/^\s*([\d]+)/', $processString, $pid);
        if (count($pid) && is_numeric($pid[0])) {
            return (int) $pid[0];
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getBroadcastId($processString): ?int
    {
        $value = $this->getMetadataValue($processString, self::METADATA_BROADCAST);

        return $value ? (int) $value: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelId($processString): ?int
    {
        $value = $this->getMetadataValue($processString, self::METADATA_CHANNEL);

        return $value ? (int) $value: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment($processString): ?string
    {
        return $this->getMetadataValue($processString, self::METADATA_ENVIRONMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getKernelEnvironment(): ?string
    {
        return $this->kernelEnvironment;
    }

    /**
     * @param string $directory
     */
    public function setFFMpegLogDirectory($directory): void
    {
        if (!is_writable($directory)) {
            return;
        }

        $this->logDirectoryFFMpeg = $directory;
    }

    /**
     * @param bool $loopable
     */
    public function setLoopable($loopable): void
    {
        $this->loopable = (bool) $loopable;
    }

    /**
     * @return bool
     */
    public function isLoopable(): bool
    {
        return $this->loopable;
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
    protected function execStreamCommand($input, $output, $meta): string
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

        $streamStart = sprintf('ffmpeg %s%s %s%s >> %s 2>&1 &', $loop, $input, $output, $meta, $logFile);

        return exec($streamStart);
    }

    /**
     * Read metadata from a process string.
     *
     * @param string $processString
     *
     * @return array
     */
    protected function readMetadata($processString): array
    {
        $processMetadata = [];
        $metadataRegex = '/-metadata ([a-z_]+)=([[:alnum:]]+)/';
        preg_match_all($metadataRegex, $processString, $metadata);

        if (count($metadata) === 3 && \is_array($metadata[1]) && \is_array($metadata[2])) {
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
}
