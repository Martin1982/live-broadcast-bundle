<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MediaRtmp
 *
 * @ORM\Table(name="broadcast_input_rtmp", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class MediaRtmp extends BaseMedia
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="rtmp_address", type="string", nullable=false)
     */
    protected $rtmpAddress;

    /**
     * @return string
     */
    public function getRtmpAddress()
    {
        return $this->rtmpAddress;
    }

    /**
     * @param string $rtmpAddress
     *
     * @return MediaRtmp
     */
    public function setRtmpAddress($rtmpAddress)
    {
        $this->rtmpAddress = str_replace('rtmp://', '', $rtmpAddress);

        return $this;
    }

    /**
     * Get input string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getRtmpAddress();
    }
}
