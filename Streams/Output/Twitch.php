<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Output;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class Twitch
 * @package Martin1982\LiveBroadcastBundle\Streams\Output
 */
class Twitch
{
    const CHANNEL_NAME = 'twitch';

    protected $server;

    protected $streamKey;

    /**
     * Twitch constructor
     */
    public function __construct($server, $streamKey)
    {
        $this->server = $server;
        $this->streamKey = $streamKey;
    }

    public function generateOutputCmd()
    {
        return sprintf('-f flv rtmp://%s/app/%s', $this->server, $this->streamKey);
    }
}