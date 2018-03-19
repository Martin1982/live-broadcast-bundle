<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Metadata;

use Doctrine\ORM\Mapping as ORM;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class YouTubeEvent
 *
 * @ORM\Table(name="live_broadcast_youtube_event", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity(repositoryClass="Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEventRepository")
 */
class YouTubeEvent
{
    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $eventId;

    /**
     * @var LiveBroadcast|null
     *
     * @ORM\ManyToOne(targetEntity="Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast")
     * @ORM\JoinColumn(name="broadcast_id", referencedColumnName="id", unique=false)
     */
    protected $broadcast;

    /**
     * @var ChannelYouTube|null
     *
     * @ORM\ManyToOne(targetEntity="Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", unique=false)
     */
    protected $channel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="youtube_id", type="string", length=128, nullable=false)
     */
    protected $youTubeId;

    /**
     * @return int|null
     */
    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    /**
     * @return LiveBroadcast|null
     */
    public function getBroadcast(): ?LiveBroadcast
    {
        return $this->broadcast;
    }

    /**
     * @param LiveBroadcast $broadcast
     *
     * @return YouTubeEvent
     */
    public function setBroadcast(LiveBroadcast $broadcast): YouTubeEvent
    {
        $this->broadcast = $broadcast;

        return $this;
    }

    /**
     * @return ChannelYouTube|null
     */
    public function getChannel(): ?ChannelYouTube
    {
        return $this->channel;
    }

    /**
     * @param ChannelYouTube $channel
     *
     * @return YouTubeEvent
     */
    public function setChannel(ChannelYouTube $channel): YouTubeEvent
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getYouTubeId(): ?string
    {
        return $this->youTubeId;
    }

    /**
     * @param string $youTubeId
     *
     * @return YouTubeEvent
     */
    public function setYouTubeId($youTubeId): YouTubeEvent
    {
        $this->youTubeId = $youTubeId;

        return $this;
    }
}
