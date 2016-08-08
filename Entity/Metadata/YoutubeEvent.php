<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Metadata;

use Doctrine\ORM\Mapping as ORM;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class YoutubeEvent
 * @package Martin1982\LiveBroadcastBundle\Entity\Metadata
 *
 * @ORM\Table(name="live_broadcast_youtube_event", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class YoutubeEvent
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $eventId;

    /**
     * @var LiveBroadcast
     *
     * @ORM\ManyToOne(targetEntity="Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast")
     * @ORM\JoinColumn(name="broadcast_id", referencedColumnName="id", unique=false)
     */
    protected $broadcast;

    /**
     * @var ChannelYoutube
     *
     * @ORM\ManyToOne(targetEntity="Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", unique=false)
     */
    protected $channel;

    /**
     * @var string
     *
     * @ORM\Column(name="youtube_id", type="string", length=128, nullable=false)
     */
    protected $youtubeId;

    /**
     * @return mixed
     */
    public function getBroadcast()
    {
        return $this->broadcast;
    }

    /**
     * @param mixed $broadcast
     * @return YoutubeEvent
     */
    public function setBroadcast($broadcast)
    {
        $this->broadcast = $broadcast;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     * @return YoutubeEvent
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getYoutubeId()
    {
        return $this->youtubeId;
    }

    /**
     * @param mixed $youtubeId
     * @return YoutubeEvent
     */
    public function setYoutubeId($youtubeId)
    {
        $this->youtubeId = $youtubeId;

        return $this;
    }
}
