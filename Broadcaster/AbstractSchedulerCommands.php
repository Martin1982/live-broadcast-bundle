<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

/**
 * Class SchedulerCommands
 * @package Martin1982\LiveBroadcastBundle\Broadcaster
 */
abstract class AbstractSchedulerCommands implements SchedulerCommandsInterface
{
    /**
     * @var string
     */
    protected $environment;

    /**
     * @var array
     */
    protected $metadata = array();

    /**
     * SchedulerCommands constructor.
     * @param string $environment
     */
    public function __construct($environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function startProcess($input, $output, $metadata)
    {
        $meta = '';
        $metadata['env'] = $this->getEnvironment();

        foreach ($metadata as $key => $value) {
            $meta .= ' -metadata '.$key.'='.$value;
        }

        $osCode = strtoupper(substr(PHP_OS, 0, 3));

        if ($osCode === 'WIN') {
            return exec(sprintf('ffmpeg %s %s%s >nul 2>nul &', $input, $output, $meta));
        }

        return exec(sprintf('ffmpeg %s %s%s >/dev/null 2>&1 &', $input, $output, $meta));
    }

    /**
     * {@inheritdoc}
     */
    public function stopProcess($pid)
    {
        throw new \Exception('stopProcess Cannot be called on the abstract class');
    }

    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses()
    {
        throw new \Exception('getRunningProcesses Cannot be called on the abstract class');
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessId($processString)
    {
        preg_match('/^\s*([\d]+)/', $processString, $pid);
        if (count($pid) && is_numeric($pid[0])) {
            return (int)$pid[0];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getBroadcastId($processString)
    {
        $metadataKey = 'broadcast_id';

        if (!count($this->metadata)) {
            $this->readMetadata($processString);
        }
        
        if (array_key_exists($metadataKey, $this->metadata)) {
            return $this->metadata[$metadataKey];
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelId($processString)
    {
        $metadataKey = 'channel_id';

        if (!count($this->metadata)) {
            $this->readMetadata($processString);
        }

        if (array_key_exists($metadataKey, $this->metadata)) {
            return $this->metadata[$metadataKey];
        }

        return;
    }

    /**
     * Read metadata from a process string
     *
     * @param $processString
     */
    protected function readMetadata($processString)
    {
        $metadataRegex = '/-metadata ([a-z_]+)=([\d]+)/';
        preg_match_all($metadataRegex, $processString, $metadata);

        if (count($metadata) === 3 && is_array($metadata[1]) && is_array($metadata[2])) {
            foreach ($metadata[1] as $metadataIndex => $metadataKey) {
                $this->metadata[$metadataKey] = $metadata[2][$metadataIndex];
            }
        }
    }

    /**
     * @return string
     */
    private function getEnvironment()
    {
        return $this->environment;
    }
}
