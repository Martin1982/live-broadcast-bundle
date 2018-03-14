<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;

/**
 * Class InputFile
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
    public function setMedia(AbstractMedia $media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return string
     *
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
