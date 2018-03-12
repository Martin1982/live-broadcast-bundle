<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class OutputYouTube
 */
class OutputYouTube implements OutputInterface
{
    /**
     * @var string
     */
    private $streamUrl;

    /**
     * @var ChannelYouTube
     */
    private $channel;

    /**
     * {@inheritdoc}
     *
     * @return OutputInterface|OutputYouTube
     */
    public function setChannel(AbstractChannel $channel): OutputInterface
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return ChannelYouTube
     */
    public function getChannel(): ChannelYouTube
    {
        return $this->channel;
    }

    /**
     * Give the cmd string to start the stream.
     *
     * @return string
     *
     * @throws LiveBroadcastOutputException
     */
    public function generateOutputCmd(): string
    {
        if (empty($this->streamUrl)) {
            throw new LiveBroadcastOutputException('The YouTube stream url must be set');
        }

        $params = '-vf scale=-1:720 -c:v libx264 -pix_fmt yuv420p ';
        $params .= '-preset veryfast -r 30 -g 60 -b:v 4000k -c:a aac -f flv "%s"';

        return sprintf(
            $params,
            $this->streamUrl
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType(): string
    {
        return ChannelYouTube::class;
    }

    /**
     * @param string $streamUrl
     */
    public function setStreamUrl($streamUrl): void
    {
        $this->streamUrl = $streamUrl;
    }
}
