<?php

namespace Martin1982\LiveBroadcastBundle\Event;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AbstractBroadcastEvent
 */
abstract class AbstractBroadcastEvent extends Event
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
