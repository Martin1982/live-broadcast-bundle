<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class OutputFacebook
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
     *
     * @return OutputInterface|OutputFacebook
     */
    public function setChannel(AbstractChannel $channel): OutputInterface
    {
        $this->channel = $channel;

        return $this;
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
            throw new LiveBroadcastOutputException('The Facebook stream url must be set');
        }

        return sprintf('-c:v libx264 -crf 30 -preset ultrafast -c:a copy -f flv "%s"', $this->streamUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType(): string
    {
        return ChannelFacebook::class;
    }

    /**
     * @return string
     *
     * @throws LiveBroadcastOutputException
     */
    public function getAccessToken(): string
    {
        if (!($this->channel instanceof ChannelFacebook)) {
            throw new LiveBroadcastOutputException(sprintf('%s Facebook channel not configured', __FUNCTION__));
        }

        return $this->channel->getAccessToken();
    }

    /**
     * @return string
     *
     * @throws LiveBroadcastOutputException
     */
    public function getEntityId(): string
    {
        if (!($this->channel instanceof ChannelFacebook)) {
            throw new LiveBroadcastOutputException(sprintf('%s Facebook channel not configured', __FUNCTION__));
        }

        return $this->channel->getFbEntityId();
    }

    /**
     * @param string $streamUrl
     */
    public function setStreamUrl($streamUrl): void
    {
        $this->streamUrl = $streamUrl;
    }
}
