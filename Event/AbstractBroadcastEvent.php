<?php

namespace Martin1982\LiveBroadcastBundle\Event;

/**
 * Class AbstractBroadcastEvent
 */
abstract class AbstractBroadcastEvent
{
    /**
     * @var LiveBroadcast
     */
    private $liveBroadcast;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * AbstractBroadcastEvent constructor
     *
     * @param LiveBroadcast   $liveBroadcast
     * @param OutputInterface $output
     */
    public function __construct(LiveBroadcast $liveBroadcast, OutputInterface $output)
    {
        $this->liveBroadcast = $liveBroadcast;
        $this->output = $output;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return LiveBroadcast
     */
    public function getLiveBroadcast()
    {
        return $this->liveBroadcast;
    }
}
