<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChannelYoutube.
 *
 * @ORM\Table(name="channel_youtube", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelYoutube extends BaseChannel
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
    protected $youtubeChannelName;

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
     * @return ChannelYoutube
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getYoutubeChannelName()
    {
        return $this->youtubeChannelName;
    }

    /**
     * @param string $youtubeChannelName
     * @return ChannelYoutube
     */
    public function setYoutubeChannelName($youtubeChannelName)
    {
        $this->youtubeChannelName = $youtubeChannelName;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Youtube: '.$this->getChannelName();
    }
}
