<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Broadcaster;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Symfony\Component\HttpKernel\Kernel;

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
    protected ?string $kernelEnvironment;

    /**
     * Directory to store FFMpeg logs
     *
     * @var string
     */
    protected string $logDirectoryFFMpeg = '';

    /**
     * @var bool Can the input be looped
     */
    protected bool $looping = false;

    /**
     * @var string
     */
    protected string $rootDir;

    /**
     * @var bool
     */
    protected bool $dryRun = false;

    /**
     * SchedulerCommands constructor.
     *
     * @param Kernel $kernel
     * @param bool   $dryRun
     */
    public function __construct(Kernel $kernel, bool $dryRun = false)
    {
        $this->dryRun = $dryRun;
        $this->rootDir = $kernel->getProjectDir();
        $this->kernelEnvironment = $kernel->getEnvironment();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function startProcess(string $input, string $output, array $metadata): string
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
    public function stopProcess(int $pid): string
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
    public function getProcessId(string $processString): ?int
    {
        $pid = [];
        preg_match('/^\s*([\d]+)/', $processString, $pid);
        if (count($pid) && is_numeric($pid[0])) {
            return (int) $pid[0];
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getBroadcastId(string $processString): ?int
    {
        $value = $this->getMetadataValue($processString, self::METADATA_BROADCAST);

        return $value ? (int) $value: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelId(string $processString): ?int
    {
        $value = $this->getMetadataValue($processString, self::METADATA_CHANNEL);

        return $value ? (int) $value: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(string $processString): ?string
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
    public function setFFMpegLogDirectory(string $directory): void
    {
        if (!is_writable($directory)) {
            return;
        }

        $this->logDirectoryFFMpeg = $directory;
    }

    /**
     * @param bool $looping
     */
    public function setLooping(bool $looping): void
    {
        $this->looping = $looping;
    }

    /**
     * @return bool
     */
    public function isLooping(): bool
    {
        return $this->looping;
    }

    /**
     * Execute the command to start the stream
     *
     * @param string $input
     * @param string $output
     * @param string $meta
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function execStreamCommand(string $input, string $output, string $meta): string
    {
        $logFile = '/dev/null';
        $loop = '';

        if (!empty($this->logDirectoryFFMpeg)) {
            $now = new \DateTime();
            $logFile = $this->logDirectoryFFMpeg.DIRECTORY_SEPARATOR.sprintf(self::LOG_FILE, $now->format('Y-m-d_His.v'));
        }

        if ($this->isLooping()) {
            $loop = '-stream_loop -1 ';
        }

        $streamStart = sprintf('ffmpeg %s%s %s%s >> %s 2>&1 &', $loop, $input, $output, $meta, $logFile);

        return $this->exec($streamStart);
    }

    /**
     * Run or dry-run a command
     *
     * @param string $command
     * @param bool   $returnOutput
     *
     * @return string|array|bool
     */
    protected function exec(string $command, bool $returnOutput = false): string|array|bool
    {
        if (true === $this->dryRun) {
            return $command;
        }

        $execReturn = exec($command, $output);

        if (true === $returnOutput) {
            return $output;
        }

        return $execReturn;
    }

    /**
     * Read metadata from a process string.
     *
     * @param string $processString
     *
     * @return array
     */
    protected function readMetadata(string $processString): array
    {
        $metadata = [];
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
    private function getMetadataValue(string $processString, string $metadataKey)
    {
        $metadata = $this->readMetadata($processString);

        return $metadata[$metadataKey] ?? null;
    }
}
