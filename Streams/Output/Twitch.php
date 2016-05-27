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

    /**
     * Twitch constructor
     */
    public function __construct()
    {
    }

    public function generateOutputCmd()
    {
        // @Todo read from config parameters
        $server = '';
        $streamKey = '';
        return sprintf('-f flv rtmp://%s/app/%s', $server, $streamKey);
    }
}