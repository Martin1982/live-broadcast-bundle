<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChannelTwitch
 *
 * @ORM\Table(name="channel_twitch", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelTwitch extends AbstractChannel
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="stream_key", type="string", length=128, nullable=false)
     *
     * @Assert\NotBlank
     */
    protected $streamKey;

    /**
     * @var string|null
     *
     * @ORM\Column(name="stream_server", type="string", length=128, nullable=false)
     *
     * @Assert\NotBlank
     */
    protected $streamServer = 'live.twitch.tv';

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
     * @return ChannelTwitch
     */
    public function setStreamKey($streamKey): ChannelTwitch
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
     * @return ChannelTwitch
     */
    public function setStreamServer($streamServer): ChannelTwitch
    {
        $this->streamServer = $streamServer;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'Twitch';
    }
}
