<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Output;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class Facebook
 * @package Martin1982\LiveBroadcastBundle\Streams\Output
 */
class Facebook implements OutputInterface
{
    const CHANNEL_NAME = 'facebook';

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $entityId;

    /**
     * @var string
     */
    private $streamUrl;

    /**
     * Facebook constructor.
     * @param ChannelFacebook $channelFacebook
     */
    public function __construct(ChannelFacebook $channelFacebook)
    {
        $this->accessToken = $channelFacebook->getAccessToken();
        $this->entityId = $channelFacebook->getFbEntityId();
    }

    /**
     * @param string $streamUrl
     */
    public function setStreamUrl($streamUrl)
    {
        $this->streamUrl = $streamUrl;
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

        return sprintf('-f flv "%s"', $this->streamUrl);
    }
}
