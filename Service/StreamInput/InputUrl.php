<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaUrl;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;

/**
 * Class InputUrl
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
    public function generateInputCmd(): string
    {
        $inputUrl = $this->media->getUrl();

        if (filter_var($inputUrl, FILTER_VALIDATE_URL) === false) {
            throw new LiveBroadcastInputException(sprintf('Invalid URL %s', $inputUrl));
        }

        return sprintf('-re -i "%s"', escapeshellarg($inputUrl));
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return MediaUrl::class;
    }
}
