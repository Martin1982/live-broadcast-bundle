<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Input;

use Doctrine\ORM\Mapping as ORM;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class InputFile.
 *
 * @ORM\Table(name="broadcast_input_file", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class InputFile extends BaseInput
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="file_location", type="string", length=128, nullable=false)
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
     * @return InputFile
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

    /**
     * @return string
     * @throws LiveBroadcastInputException
     */
    public function generateInputCmd()
    {
        $inputFilename = $this->getFileLocation();

        if (!file_exists($inputFilename)) {
            throw new LiveBroadcastInputException(sprintf('Could not find input file %s', $inputFilename));
        }

        return sprintf('-re -i %s', escapeshellarg($inputFilename));
    }
}
