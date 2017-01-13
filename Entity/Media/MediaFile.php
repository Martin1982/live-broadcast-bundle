<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MediaFile
 * @package Martin1982\LiveBroadcastBundle\Entity\Media
 *
 * @ORM\Table(name="broadcast_input_file", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class MediaFile extends BaseMedia
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="file_location", type="string", nullable=false)
     */
    protected $fileLocation;

    /**
     * @return string
     */
    public function getFileLocation()
    {
        return $this->fileLocation;
    }

    /**
     * @param string $fileLocation
     *
     * @return MediaFile
     */
    public function setFileLocation($fileLocation)
    {
        $this->fileLocation = $fileLocation;

        return $this;
    }

    /**
     * Get input string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getFileLocation();
    }
}
