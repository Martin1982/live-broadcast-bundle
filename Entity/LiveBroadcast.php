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
    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $broadcastId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var UploadedFile|File|null
     *
     * @ORM\Column(name="thumbnail", type="string", length=255, nullable=true)
     *
     * @Assert\Image(minRatio="1.78", maxRatio="1.78", minWidth="1280", minHeight="720", maxSize="5120k")
     */
    private $thumbnail;

    /**
     * @var AbstractMedia|null
     *
     * @ORM\OneToOne(targetEntity="Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia")
     * @ORM\JoinColumn(name="input_id", referencedColumnName="id")
     */
    private $input;

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
     * @ORM\Column(name="stop_on_end_timestamp", type="boolean", nullable=false)
     */
    private $stopOnEndTimestamp = true;

    /**
     * @var AbstractChannel[]
     *
     * @ORM\ManyToMany(targetEntity="Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel")
     * @ORM\JoinTable(name="broadcasts_channels",
     *      joinColumns={@ORM\JoinColumn(name="broadcast_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id")}
     * )
     */
    private $outputChannels;

    /**
     * LiveBroadcast constructor
     */
    public function __construct()
    {
        $this->outputChannels = new ArrayCollection();
        $this->setStartTimestamp(new \DateTime());
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
     * @param string $name
     *
     * @return LiveBroadcast
     */
    public function setName($name): LiveBroadcast
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
     * @param string $description
     *
     * @return LiveBroadcast
     */
    public function setDescription($description): LiveBroadcast
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return UploadedFile|File|null
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param string|UploadedFile|File $thumbnail
     *
     * @return LiveBroadcast
     */
    public function setThumbnail($thumbnail): LiveBroadcast
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
    public function setStartTimestamp($startTimestamp): LiveBroadcast
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
    public function setEndTimestamp($endTimestamp): LiveBroadcast
    {
        $this->endTimestamp = $endTimestamp;

        return $this;
    }

    /**
     * @param AbstractChannel[] $channels
     *
     * @return LiveBroadcast
     */
    public function setOutputChannels($channels): self
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
    public function getOutputChannels()
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
    public function setStopOnEndTimestamp($stopOnEndTimestamp): LiveBroadcast
    {
        $this->stopOnEndTimestamp = $stopOnEndTimestamp;

        return $this;
    }
}
