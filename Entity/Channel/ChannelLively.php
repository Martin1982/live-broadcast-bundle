<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChannelLively
 *
 * @ORM\Table(name="channel_lively", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelLively extends AbstractChannel
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="stream_key", type="string", length=128, nullable=false)
     */
    protected $streamKey;

    /**
     * @var string|null
     *
     * @ORM\Column(name="stream_server", type="string", length=128, nullable=false)
     */
    protected $streamServer;

    /**
     * @return string|null
     */
    public function getStreamKey(): ?string
    {
        return $this->streamKey;
    }

    /**
     * @param string $streamKey
     *
     * @return ChannelLively
     */
    public function setStreamKey($streamKey): ChannelLively
    {
        $this->streamKey = $streamKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreamServer(): ?string
    {
        return $this->streamServer;
    }

    /**
     * @param string $streamServer
     *
     * @return ChannelLively
     */
    public function setStreamServer($streamServer): ChannelLively
    {
        $this->streamServer = $streamServer;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Live.ly: '.$this->getChannelName();
    }
}
