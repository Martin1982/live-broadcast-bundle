<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChannelUstream
 * @package Martin1982\LiveBroadcastBundle\Entity\Channel
 *
 * @ORM\Table(name="channel_ustream", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelUstream extends BaseChannel
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
    protected $streamServer;

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
     * @return ChannelUstream
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
     * @return ChannelUstream
     */
    public function setStreamServer($streamServer)
    {
        $this->streamServer = $streamServer;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Ustream: '.$this->getChannelName();
    }
}
