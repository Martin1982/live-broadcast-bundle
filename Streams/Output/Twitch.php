<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Output;

/**
 * Class Twitch
 * @package Martin1982\LiveBroadcastBundle\Streams\Output
 */
class Twitch implements OutputInterface
{
    const CHANNEL_NAME = 'twitch';

    /**
     * @var string
     */
    protected $server;

    /**
     * @var string
     */
    protected $streamKey;

    /**
     * Twitch constructor
     */
    public function __construct($server, $streamKey)
    {
        $this->server = $server;
        $this->streamKey = $streamKey;
    }

    /**
     * Get the output parameters for streaming
     *
     * @return string
     */
    public function generateOutputCmd()
    {
        return sprintf('-f flv rtmp://%s/app/%s', $this->server, $this->streamKey);
    }
}
