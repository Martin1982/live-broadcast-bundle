<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Input;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class InputFile
 * @package Martin1982\LiveBroadcastBundle\Entity\Input
 *
 * @ORM\Table(name="broadcast_input_file", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class InputFile extends BaseInput
{
    /**
     * @var string
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
     * @return InputFile
     */
    public function setFileLocation($fileLocation)
    {
        $this->fileLocation = $fileLocation;

        return $this;
    }
}

