<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Metadata;

use Doctrine\ORM\Mapping as ORM;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class StreamEvent
 *
 * @ORM\Table(name="live_broadcast_stream_event", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity(repositoryClass="StreamEventRepository")
 */
class StreamEvent
{
    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private ?int $eventId = null;

    /**
     * @var LiveBroadcast|null
     *
     * @ORM\ManyToOne(targetEntity="Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast")
     * @ORM\JoinColumn(name="broadcast_id", referencedColumnName="id", unique=false, onDelete="CASCADE")
     */
    protected ?LiveBroadcast $broadcast;

    /**
     * @var AbstractChannel|null
     *
     * @ORM\ManyToOne(targetEntity="Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", unique=false, onDelete="CASCADE")
     */
    protected ?AbstractChannel $channel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="external_stream_id", type="string", length=128, nullable=false)
     */
    protected ?string $externalStreamId = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="end_signal_sent", type="boolean", nullable=true)
     */
    protected bool $endSignalSent = false;

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
     * @return StreamEvent
     */
    public function setBroadcast(LiveBroadcast $broadcast): StreamEvent
    {
        $this->broadcast = $broadcast;

        return $this;
    }

    /**
     * @return AbstractChannel|null
     */
    public function getChannel(): ?AbstractChannel
    {
        return $this->channel;
    }

    /**
     * @param AbstractChannel $channel
     *
     * @return StreamEvent
     */
    public function setChannel(AbstractChannel $channel): StreamEvent
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalStreamId(): ?string
    {
        return $this->externalStreamId;
    }

    /**
     * @param string|null $externalStreamId
     *
     * @return StreamEvent
     */
    public function setExternalStreamId(?string $externalStreamId): StreamEvent
    {
        $this->externalStreamId = $externalStreamId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEndSignalSent(): bool
    {
        return $this->endSignalSent;
    }

    /**
     * @param bool $endSignalSent
     *
     * @return StreamEvent
     */
    public function setEndSignalSent(bool $endSignalSent): StreamEvent
    {
        $this->endSignalSent = $endSignalSent;

        return $this;
    }
}
