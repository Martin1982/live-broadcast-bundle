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
     * @ORM\Column(name="access_token", type="string", length=255, nullable=false)
     */
    protected $accessToken;

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     *
     * @return ChannelYoutube
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

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
