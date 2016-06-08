<?php

namespace Martin1982\LiveBroadcastBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Input\BaseInput;

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
     * @var int
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
     * @var BaseInput
     *
     * @ORM\OneToOne(targetEntity="Martin1982\LiveBroadcastBundle\Entity\Input\BaseInput")
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
     * @var BaseChannel
     *
     * @ORM\ManyToMany(targetEntity="Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel")
     * @ORM\JoinTable(name="broadcasts_channels",
     *      joinColumns={@ORM\JoinColumn(name="broadcast_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id", unique=true)}
     * )
     */
    private $outputChannels;

    /**
     * LiveBroadcast constructor.
     */
    public function __construct() {
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
        return $this->getBroadcastId() === null ? '-' : $this->getName();
    }

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
     *
     * @return LiveBroadcast
     */
    public function setName($name)
    {
        $this->name = $name;

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
     *
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
     *
     * @return LiveBroadcast
     */
    public function setEndTimestamp($endTimestamp)
    {
        $this->endTimestamp = $endTimestamp;

        return $this;
    }

    /**
     * @param $channels
     * @return $this
     */
    public function setOutputChannels($channels)
    {
        if (count($channels) > 0) {
            foreach ($channels as $channel) {
                $this->addOutputChannel($channel);
            }
        }

        return $this;
    }

    /**
     * @param BaseChannel $channel
     * @return $this
     */
    public function addOutputChannel(BaseChannel $channel)
    {
        $this->outputChannels->add($channel);

        return $this;
    }

    /**
     * @param BaseChannel $channel
     * @return $this
     */
    public function removeOutputChannel(BaseChannel $channel)
    {
        $this->outputChannels->remove($channel);

        return $this;
    }

    /**
     * @return BaseChannel[]
     */
    public function getOutputChannels()
    {
        return $this->outputChannels;
    }

    /**
     * @return BaseInput
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param BaseInput $input
     * @return LiveBroadcast
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }
}
