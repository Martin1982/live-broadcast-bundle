<?php

namespace Martin1982\LiveBroadcastBundle\Event;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Streams\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PreBroadcastEvent
 * @package Martin1982\LiveBroadcastBundle\Event
 */
class PreBroadcastEvent extends Event
{
    const NAME = 'martin1982.livebroadcast.pre_broadcast';

    /**
     * @var LiveBroadcast
     */
    private $liveBroadcast;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * PreBroadcastEvent constructor.
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
