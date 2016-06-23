<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Output;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class Facebook.
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
    private $applicationId;

    /**
     * @var string
     */
    private $applicationSecret;

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
        $this->applicationId = $channelFacebook->getApplicationId();
        $this->applicationSecret = $channelFacebook->getApplicationSecret();
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /**
     * @return string
     */
    public function getApplicationSecret()
    {
        return $this->applicationSecret;
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

        return sprintf('-c:v libx264 -crf 18 -vf scale=-1:720 -preset slow -c:a copy -f flv "%s"', $this->streamUrl);
    }
}
