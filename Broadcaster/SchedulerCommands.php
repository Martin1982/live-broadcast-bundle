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
        preg_match('/^[\d]+/', $processString, $pid);
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
        preg_match('/env='.$this->environment.' -metadata broadcast_id=[\d]+/', $processString, $broadcast);
        if (is_array($broadcast) && !empty($broadcast) && is_string($broadcast[0])) {
            $broadcastDetails = explode('=', $broadcast[0]);
            return end($broadcastDetails);
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelId($processString)
    {
        preg_match('/env='.$this->environment.' -metadata broadcast_id=([\d]+) channel_id=[\d]+/', $processString, $ids);
        if (is_array($ids) && !empty($ids) && is_string($ids[1])) {
            return $ids[1];
        }

        return;
    }

    /**
     * @return string
     */
    private function getEnvironment()
    {
        return $this->environment;
    }
}
