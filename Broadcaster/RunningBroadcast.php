<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

/**
 * Class RunningBroadcast
 * @package Martin1982\LiveBroadcastBundle\Broadcaster
 */
class RunningBroadcast
{
    /**
     * @var int
     */
    private $broadcastId;

    /**
     * @var int
     */
    private $processId;

    /**
     * RunningBroadcast constructor.
     * @param int $broadcastId
     * @param int $processId
     */
    public function __construct($broadcastId, $processId)
    {
        $this->broadcastId = $broadcastId;
        $this->processId = $processId;
    }

    /**
     * @return int
     */
    public function getBroadcastId()
    {
        return $this->broadcastId;
    }

    /**
     * @return int
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return ($this->getProcessId() !== null) && ($this->getBroadcastId() !== null);
    }
}
