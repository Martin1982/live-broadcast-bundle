<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

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
     * @throws LiveBroadcastOutputException
     * @return string
     */
    public function generateOutputCmd()
    {
        if (empty($this->streamUrl)) {
            throw new LiveBroadcastOutputException('The YouTube stream url must be set');
        }

        $params = '-vf scale=-1:720 -c:v libx264 -pix_fmt yuv420p ';
        $params.= '-preset veryfast -r 30 -g 60 -b:v 4000k -c:a aac -f flv "%s"';

        return sprintf(
            $params,
            $this->streamUrl
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType()
    {
        return ChannelYoutube::class;
    }

    /**
     * @param string $streamUrl
     */
    public function setStreamUrl($streamUrl)
    {
        $this->streamUrl = $streamUrl;
    }
}
