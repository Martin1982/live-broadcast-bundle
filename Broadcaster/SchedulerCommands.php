<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

/**
 * Class SchedulerCommands
 * @package Martin1982\LiveBroadcastBundle\Broadcaster
 */
class SchedulerCommands implements SchedulerCommandsInterface
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

        return exec(sprintf('ffmpeg %s %s%s >/dev/null 2>&1 &', $input, $output, $meta));
    }

    /**
     * {@inheritdoc}
     */
    public function stopProcess($pid)
    {
        return exec(sprintf("kill %d", $pid));
    }

    /**
     * {@inheritdoc}
     */
    public function getRunningProcesses()
    {
        exec('/bin/ps -C ffmpeg -o pid=,args=', $output);

        return $output;
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
