<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class OutputFacebook
 * @package Martin1982\LiveBroadcastBundle\Service\StreamOutput
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
     * @throws LiveBroadcastOutputException
     * @return string
     */
    public function generateOutputCmd()
    {
        if (empty($this->streamUrl)) {
            throw new LiveBroadcastOutputException('The Facebook stream url must be set');
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
     * @throws LiveBroadcastOutputException
     */
    public function getAccessToken()
    {
        if (!($this->channel instanceof ChannelFacebook)) {
            throw new LiveBroadcastOutputException(__FUNCTION__.' Facebook channel not configured');
        }

        return $this->channel->getAccessToken();
    }

    /**
     * @return string
     * @throws LiveBroadcastOutputException
     */
    public function getEntityId()
    {
        if (!($this->channel instanceof ChannelFacebook)) {
            throw new LiveBroadcastOutputException(__FUNCTION__.' Facebook channel not configured');
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
