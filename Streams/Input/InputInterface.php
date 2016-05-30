<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Input;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

interface InputInterface
{
    /**
     * InputInterface constructor.
     * @param LiveBroadcast $broadcast
     */
    public function __construct(LiveBroadcast $broadcast);

    /**
     * @return string
     */
    public function generateInputCmd();
}