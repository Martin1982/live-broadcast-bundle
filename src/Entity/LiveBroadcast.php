<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LiveBroadcast
 *
 * @ORM\Table(name="live_broadcast", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity(repositoryClass="Martin1982\LiveBroadcastBundle\Entity\LiveBroadcastRepository")
 */
class LiveBroadcast
{
    public const PRIVACY_STATUS_PUBLIC = 0;
    public const PRIVACY_STATUS_PRIVATE = 1;
    public const PRIVACY_STATUS_UNLISTED = 2;

    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private ?int $broadcastId = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     *
     * @Assert\NotBlank
     */
    private ?string $name = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private ?string $description = null;

    /**
     * @var string|File|UploadedFile|null
     *
     * @ORM\Column(name="thumbnail", type="string", length=255, nullable=true)
     *
     * @Assert\Image(minRatio="1.78", maxRatio="1.78", minWidth="1280", minHeight="720", maxSize="5120k")
     */
    private UploadedFile|File|null|string $thumbnail = null;

    /**
     * @var AbstractMedia|null
     *
     * @ORM\ManyToOne(targetEntity="Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia")
     * @ORM\JoinColumn(name="input_id", referencedColumnName="id")
     */
    private ?AbstractMedia $input;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="broadcast_start", type="datetime", nullable=false)
     *
     * @Assert\GreaterThan("+30 seconds")
     */
    private \DateTime $startTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="broadcast_end", type="datetime", nullable=false)
     *
     * @Assert\GreaterThan("+1 minute")
     */
    private \DateTime $endTimestamp;

    /**
     * @var int
     *
     * @ORM\Column(name="privacy_status", type="integer", options={"default": 0})
     */
    private int $privacyStatus = self::PRIVACY_STATUS_PUBLIC;

    /**
     * @var bool
     *
     * @ORM\Column(name="stop_on_end_timestamp", type="boolean", nullable=false)
     */
    private bool $stopOnEndTimestamp = true;

    /**
     * @var AbstractChannel[]
     *
     * @ORM\ManyToMany(targetEntity="Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel")
     * @ORM\JoinTable(name="broadcasts_channels",
     *      joinColumns={@ORM\JoinColumn(name="broadcast_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id")}
     * )
     */
    private array|ArrayCollection $outputChannels;

    /**
     * LiveBroadcast constructor
     */
    public function __construct()
    {
        $this->outputChannels = new ArrayCollection();
        $this->setStartTimestamp(new \DateTime('+15 minutes'));
        $this->setEndTimestamp(new \DateTime('+1 hour'));
    }

    /**
     * Return the string representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getBroadcastId() === null ? '-' : (string) $this->getName();
    }

    /**
     * @return int|null
     */
    public function getBroadcastId(): ?int
    {
        return $this->broadcastId;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return LiveBroadcast
     */
    public function setName(?string $name): LiveBroadcast
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return LiveBroadcast
     */
    public function setDescription(?string $description): LiveBroadcast
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return File|UploadedFile|null|string
     */
    public function getThumbnail(): File|UploadedFile|null|string
    {
        return $this->thumbnail;
    }

    /**
     * @param string|File|UploadedFile $thumbnail
     *
     * @return LiveBroadcast
     */
    public function setThumbnail(File|string|UploadedFile $thumbnail): LiveBroadcast
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartTimestamp(): \DateTime
    {
        return $this->startTimestamp;
    }

    /**
     * @param \DateTime $startTimestamp
     *
     * @return LiveBroadcast
     */
    public function setStartTimestamp(\DateTime $startTimestamp): LiveBroadcast
    {
        $this->startTimestamp = $startTimestamp;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndTimestamp(): \DateTime
    {
        return $this->endTimestamp;
    }

    /**
     * @param \DateTime $endTimestamp
     *
     * @return LiveBroadcast
     */
    public function setEndTimestamp(\DateTime $endTimestamp): LiveBroadcast
    {
        $this->endTimestamp = $endTimestamp;

        return $this;
    }

    /**
     * @param AbstractChannel[] $channels
     *
     * @return LiveBroadcast
     */
    public function setOutputChannels(array $channels = []): self
    {
        if (count($channels) > 0) {
            foreach ($channels as $channel) {
                $this->addOutputChannel($channel);
            }
        }

        return $this;
    }

    /**
     * @param AbstractChannel $channel
     *
     * @return LiveBroadcast
     */
    public function addOutputChannel(AbstractChannel $channel): LiveBroadcast
    {
        $this->outputChannels->add($channel);

        return $this;
    }

    /**
     * @param AbstractChannel $channel
     *
     * @return LiveBroadcast
     */
    public function removeOutputChannel(AbstractChannel $channel): LiveBroadcast
    {
        $this->outputChannels->removeElement($channel);

        return $this;
    }

    /**
     * @return ArrayCollection|AbstractChannel[]
     */
    public function getOutputChannels(): ArrayCollection|array
    {
        return $this->outputChannels;
    }

    /**
     * @return AbstractMedia|null
     */
    public function getInput(): ?AbstractMedia
    {
        return $this->input;
    }

    /**
     * @param AbstractMedia $input
     *
     * @return LiveBroadcast
     */
    public function setInput(AbstractMedia $input): LiveBroadcast
    {
        $this->input = $input;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isStopOnEndTimestamp(): bool
    {
        return $this->stopOnEndTimestamp;
    }

    /**
     * @param boolean $stopOnEndTimestamp
     *
     * @return LiveBroadcast
     */
    public function setStopOnEndTimestamp(bool $stopOnEndTimestamp): LiveBroadcast
    {
        $this->stopOnEndTimestamp = $stopOnEndTimestamp;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrivacyStatus(): int
    {
        return $this->privacyStatus;
    }

    /**
     * @param int $privacyStatus
     *
     * @return LiveBroadcast
     */
    public function setPrivacyStatus(int $privacyStatus): LiveBroadcast
    {
        $this->privacyStatus = $privacyStatus;

        return $this;
    }
}
