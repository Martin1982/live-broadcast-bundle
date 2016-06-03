<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Output;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;

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
     * Twitch constructor.
     *
     * @param ChannelTwitch $channel
     */
    public function __construct(ChannelTwitch $channel)
    {
        $this->server = $channel->getStreamServer();
        $this->streamKey = $channel->getStreamKey();
    }

    /**
     * Get the output parameters for streaming.
     *
     * @return string
     */
    public function generateOutputCmd()
    {
        return sprintf('-f flv rtmp://%s/app/%s', $this->server, $this->streamKey);
    }
}
