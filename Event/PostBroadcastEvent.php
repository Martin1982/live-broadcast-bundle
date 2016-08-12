<?php

namespace Martin1982\LiveBroadcastBundle\Event;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PostBroadcastEvent
 * @package Martin1982\LiveBroadcastBundle\Event
 */
class PostBroadcastEvent extends Event
{
    const NAME = 'martin1982.livebroadcast.post_broadcast';

    /**
     * @var LiveBroadcast
     */
    private $liveBroadcast;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * PostBroadcastEvent constructor.
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
