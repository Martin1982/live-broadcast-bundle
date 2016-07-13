<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class OutputTwitch.
 */
class OutputTwitch implements OutputInterface
{
    /**
     * @var ChannelTwitch
     */
    protected $channel;

    /**
     * {@inheritdoc}
     */
    public function setChannel(BaseChannel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get the output parameters for streaming.
     *
     * @return string
     * @throws LiveBroadcastException
     */
    public function generateOutputCmd()
    {
        if ((!($this->channel instanceof ChannelTwitch)) ||
            empty($this->channel->getStreamKey()) ||
            empty($this->channel->getStreamServer())) {
            throw new LiveBroadcastException(__FUNCTION__.' Twitch channel not configured');
        }

        return sprintf('-vcodec copy -acodec copy -f flv "rtmp://%s/app/%s"',
            $this->channel->getStreamServer(), $this->channel->getStreamKey());
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType()
    {
        return ChannelTwitch::class;
    }
}
