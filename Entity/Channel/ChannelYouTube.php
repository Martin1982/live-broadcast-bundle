<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChannelYouTube.
 *
 * @ORM\Table(name="channel_youtube", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelYouTube extends BaseChannel
{
    /**
     * @var string
     *
     * @ORM\Column(name="refresh_token", type="string", length=255, nullable=false)
     */
    protected $refreshToken;

    /**
     * @var string
     *
     * @ORM\Column(name="youtube_channel_name", type="string", length=255, nullable=false)
     */
    protected $youTubeChannelName;

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     *
     * @return ChannelYouTube
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getYouTubeChannelName()
    {
        return $this->youTubeChannelName;
    }

    /**
     * @param string $youTubeChannelName
     * @return ChannelYouTube
     */
    public function setYouTubeChannelName($youTubeChannelName)
    {
        $this->youTubeChannelName = $youTubeChannelName;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'YouTube: '.$this->getChannelName();
    }
}
