<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\BaseMedia;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaUrl;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;

/**
 * Class InputUrl
 * @package Martin1982\LiveBroadcastBundle\Service\StreamInput
 */
class InputUrl implements InputInterface
{
    /**
     * @var MediaUrl
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
        $inputUrl = $this->media->getUrl();

        if (filter_var($inputUrl, FILTER_VALIDATE_URL) === false) {
            throw new LiveBroadcastInputException(sprintf('Invalid URL %s', $inputUrl));
        }

        return sprintf('-re -i %s', escapeshellarg($inputUrl));
    }

    /**
     * @return string
     */
    public function getMediaType()
    {
        return MediaUrl::class;
    }
}
