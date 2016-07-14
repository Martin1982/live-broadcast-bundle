<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class OutputFacebook.
 */
class OutputFacebook implements OutputInterface
{
    /**
     * @var string
     */
    private $streamUrl;

    /**
     * @var ChannelFacebook
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
     * Give the cmd string to start the stream.
     *
     * @throws LiveBroadcastException
     * @return string
     */
    public function generateOutputCmd()
    {
        if (empty($this->streamUrl)) {
            throw new LiveBroadcastException('The Facebook stream url must be set');
        }

        return sprintf('-c:v libx264 -crf 30 -preset ultrafast -c:a copy -f flv "%s"', $this->streamUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType()
    {
        return ChannelFacebook::class;
    }

    /**
     * @return string
     * @throws LiveBroadcastException
     */
    public function getAccessToken()
    {
        if (!($this->channel instanceof ChannelFacebook)) {
            throw new LiveBroadcastException(__FUNCTION__.' Facebook channel not configured');
        }

        return $this->channel->getAccessToken();
    }

    /**
     * @return string
     * @throws LiveBroadcastException
     */
    public function getEntityId()
    {
        if (!($this->channel instanceof ChannelFacebook)) {
            throw new LiveBroadcastException(__FUNCTION__.' Facebook channel not configured');
        }

        return $this->channel->getFbEntityId();
    }

    /**
     * @param string $streamUrl
     */
    public function setStreamUrl($streamUrl)
    {
        $this->streamUrl = $streamUrl;
    }
}
