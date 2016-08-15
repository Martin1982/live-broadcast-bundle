<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChannelTwitch
 * @package Martin1982\LiveBroadcastBundle\Entity\Channel
 *
 * @ORM\Table(name="channel_twitch", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelTwitch extends BaseChannel
{
    /**
     * @var string
     *
     * @ORM\Column(name="stream_key", type="string", length=128, nullable=false)
     */
    protected $streamKey;

    /**
     * @var string
     *
     * @ORM\Column(name="stream_server", type="string", length=128, nullable=false)
     */
    protected $streamServer = 'live.twitch.tv';

    /**
     * @return string
     */
    public function getStreamKey()
    {
        return $this->streamKey;
    }

    /**
     * @param string $streamKey
     *
     * @return ChannelTwitch
     */
    public function setStreamKey($streamKey)
    {
        $this->streamKey = $streamKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreamServer()
    {
        return $this->streamServer;
    }

    /**
     * @param string $streamServer
     *
     * @return ChannelTwitch
     */
    public function setStreamServer($streamServer)
    {
        $this->streamServer = $streamServer;

        return $this;
    }

    public function __toString()
    {
        return 'Twitch: '.$this->getChannelName();
    }
}
