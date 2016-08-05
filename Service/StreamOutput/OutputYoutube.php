<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class OutputYoutube.
 */
class OutputYoutube implements OutputInterface
{
    /**
     * @var string
     */
    private $streamUrl;

    /**
     * @var ChannelYoutube
     */
    private $channel;

    /**
     * {@inheritdoc}
     */
    public function setChannel(BaseChannel $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return ChannelYoutube
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Give the cmd string to start the stream.
     *
     * @throws LiveBroadcastException
     * @return string
     */
    public function generateOutputCmd()
    {
        if (empty($this->streamUrl)) {
            throw new LiveBroadcastException('The YouTube stream url must be set');
        }

        return sprintf('-c:v libx264 -crf 30 -preset ultrafast -c:a copy -f flv "%s"', $this->streamUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType()
    {
        return ChannelYoutube::class;
    }

    /**
     * @return string
     * @throws LiveBroadcastException
     */
    public function getAccessToken()
    {
        if (!($this->channel instanceof ChannelYoutube)) {
            throw new LiveBroadcastException(__FUNCTION__.' YouTube channel not configured');
        }

        return $this->channel->getRefreshToken();
    }

    /**
     * @param string $streamUrl
     */
    public function setStreamUrl($streamUrl)
    {
        $this->streamUrl = $streamUrl;
    }
}
