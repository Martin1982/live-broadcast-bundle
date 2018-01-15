<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\BaseMedia;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;

/**
 * Class InputFile
 * @package Martin1982\LiveBroadcastBundle\Service\StreamInput
 */
class InputFile implements InputInterface
{
    /**
     * @var MediaFile
     */
    private $media;

    /**
     * {@inheritdoc}
     */
    public function setMedia(BaseMedia $media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return string
     * @throws LiveBroadcastInputException
     */
    public function generateInputCmd()
    {
        $inputFilename = $this->media->getFileLocation();

        if (!file_exists($inputFilename)) {
            throw new LiveBroadcastInputException(sprintf('Could not find input file %s', $inputFilename));
        }

        return sprintf('-re -i %s', escapeshellarg($inputFilename));
    }

    /**
     * @return string
     */
    public function getMediaType()
    {
        return MediaFile::class;
    }
}
