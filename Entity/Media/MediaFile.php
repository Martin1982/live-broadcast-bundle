<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MediaFile
 *
 * @ORM\Table(name="broadcast_input_file", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class MediaFile extends AbstractMedia
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="file_location", type="string", nullable=false)
     */
    protected $fileLocation;

    /**
     * @return string|null
     */
    public function getFileLocation(): ?string
    {
        return $this->fileLocation;
    }

    /**
     * @param string $fileLocation
     *
     * @return MediaFile
     */
    public function setFileLocation($fileLocation): MediaFile
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
