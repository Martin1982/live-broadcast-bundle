<?php

namespace Martin1982\LiveBroadcastBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class LiveBroadcast
 * @package Martin1982\LiveBroadcastBundle\Entity
 *
 * @ORM\Table(name="live_broadcast", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class LiveBroadcast
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $broadcastId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="video_input_file", type="string", length=128)
     */
    private $videoInputFile;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="broadcast_start", type="datetime", nullable=false)
     */
    private $startTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="broadcast_end", type="datetime", nullable=false)
     */
    private $endTimestamp;

    /**
     * @var bool
     *
     * @ORM\Column(name="live_on_youtube", type="boolean", nullable=false)
     */
    private $liveOnYoutube = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="live_on_twitch", type="boolean", nullable=false)
     */
    private $liveOnTwitch = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="live_on_facebook", type="boolean", nullable=false)
     */
    private $liveOnFacebook = false;

    /**
     * @return int
     */
    public function getBroadcastId()
    {
        return $this->broadcastId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return LiveBroadcast
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getVideoInputFile()
    {
        return $this->videoInputFile;
    }

    /**
     * @param string $videoInputFile
     * @return LiveBroadcast
     */
    public function setVideoInputFile($videoInputFile)
    {
        $this->videoInputFile = $videoInputFile;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartTimestamp()
    {
        return $this->startTimestamp;
    }

    /**
     * @param \DateTime $startTimestamp
     * @return LiveBroadcast
     */
    public function setStartTimestamp($startTimestamp)
    {
        $this->startTimestamp = $startTimestamp;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndTimestamp()
    {
        return $this->endTimestamp;
    }

    /**
     * @param \DateTime $endTimestamp
     * @return LiveBroadcast
     */
    public function setEndTimestamp($endTimestamp)
    {
        $this->endTimestamp = $endTimestamp;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLiveOnYoutube()
    {
        return $this->liveOnYoutube;
    }

    /**
     * @param bool $liveOnYoutube
     * @return LiveBroadcast
     */
    public function setLiveOnYoutube($liveOnYoutube)
    {
        $this->liveOnYoutube = $liveOnYoutube;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLiveOnTwitch()
    {
        return $this->liveOnTwitch;
    }

    /**
     * @param bool $liveOnTwitch
     * @return LiveBroadcast
     */
    public function setLiveOnTwitch($liveOnTwitch)
    {
        $this->liveOnTwitch = $liveOnTwitch;

        return $this;
    }

    /**
     * @return bool
     */
    public function getLiveOnFacebook()
    {
        return $this->liveOnFacebook;
    }

    /**
     * @param bool $liveOnFacebook
     * @return LiveBroadcast
     */
    public function setLiveOnFacebook($liveOnFacebook)
    {
        $this->liveOnFacebook = $liveOnFacebook;

        return $this;
    }
}