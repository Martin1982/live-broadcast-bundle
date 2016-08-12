<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Metadata;

use Doctrine\ORM\Mapping as ORM;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class YoutubeEvent
 * @package Martin1982\LiveBroadcastBundle\Entity\Metadata
 *
 * @ORM\Table(name="live_broadcast_youtube_event", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity(repositoryClass="Martin1982\LiveBroadcastBundle\Entity\Metadata\YoutubeEventRepository")
 */
class YoutubeEvent
{

    const STATE_LOCAL_CREATED = 0;
    const STATE_LOCAL_READY = 1;
    const STATE_LOCAL_TEST_STARTING = 2;
    const STATE_LOCAL_TESTING = 3;
    const STATE_LOCAL_REVOKED = 4;
    const STATE_LOCAL_ABANDONED = 5;
    const STATE_LOCAL_LIVE_STARTING = 6;
    const STATE_LOCAL_LIVE = 7;
    const STATE_LOCAL_RECLAIMED = 8;
    const STATE_LOCAL_COMPLETE = 9;
    
    const STATE_REMOTE_CREATED = 'created';
    const STATE_REMOTE_READY = 'ready';
    const STATE_REMOTE_TEST_STARTING = 'testStarting';
    const STATE_REMOTE_TESTING = 'testing';
    const STATE_REMOTE_REVOKED = 'revoked';
    const STATE_REMOTE_ABANDONED = 'abandoned';
    const STATE_REMOTE_LIVE_STARTING = 'liveStarting';
    const STATE_REMOTE_LIVE = 'live';
    const STATE_REMOTE_RECLAIMED = 'reclaimed';
    const STATE_REMOTE_COMPLETE = 'complete';

    /**
     * Remote / local state mapping
     *
     * @var array
     */
    private $stateMapping = array(
        self::STATE_LOCAL_CREATED => self::STATE_REMOTE_CREATED,
        self::STATE_LOCAL_READY => self::STATE_REMOTE_READY,
        self::STATE_LOCAL_TEST_STARTING => self::STATE_REMOTE_TEST_STARTING,
        self::STATE_LOCAL_TESTING => self::STATE_REMOTE_TESTING,
        self::STATE_LOCAL_REVOKED => self::STATE_REMOTE_REVOKED,
        self::STATE_LOCAL_ABANDONED => self::STATE_REMOTE_ABANDONED,
        self::STATE_LOCAL_LIVE_STARTING => self::STATE_REMOTE_LIVE_STARTING,
        self::STATE_LOCAL_LIVE => self::STATE_REMOTE_LIVE,
        self::STATE_LOCAL_RECLAIMED => self::STATE_REMOTE_RECLAIMED,
        self::STATE_LOCAL_COMPLETE => self::STATE_REMOTE_COMPLETE,
    );

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
     * @var string
     *
     * @ORM\Column(name="last_known_state", type="integer", nullable=true)
     */
    protected $lastKnownState = 0;

    /**
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return LiveBroadcast
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
     * @return ChannelYoutube
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

    /**
     * @return string
     */
    public function getLastKnownState()
    {
        return $this->lastKnownState;
    }

    /**
     * @param string $lastKnownState
     * @return YoutubeEvent
     */
    public function setLastKnownState($lastKnownState)
    {
        $this->lastKnownState = $lastKnownState;

        return $this;
    }

    /**
     * @param $remoteState
     * @return mixed
     * @throws LiveBroadcastOutputException
     */
    public function getLocalStateByRemoteState($remoteState)
    {
        if (!in_array($remoteState, $this->stateMapping, true)) {
            throw new LiveBroadcastOutputException(sprintf('Invalid remote state \'%s\'', $remoteState));
        }

        return array_search($remoteState, $this->stateMapping, true);
    }

    /**
     * @param $localState
     * @return mixed
     * @throws LiveBroadcastOutputException
     */
    public function getRemoteStateByLocalState($localState)
    {
        if (!array_key_exists($localState, $this->stateMapping)) {
            throw new LiveBroadcastOutputException(sprintf('Invalid local state \'%s\'', $localState));
        }

        return $this->stateMapping[$localState];
    }
}
