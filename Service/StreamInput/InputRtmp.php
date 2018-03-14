<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaRtmp;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;

/**
 * Class InputRtmp
 */
class InputRtmp implements InputInterface
{
    /**
     * @var MediaRtmp
     */
    private $media;

    /**
     * @return string
     *
     * @throws LiveBroadcastInputException
     */
    public function generateInputCmd()
    {
        $inputStream = $this->media->getRtmpAddress();
        $host = parse_url('http://'.$inputStream, PHP_URL_HOST);

        if (!@fsockopen($host, 1935)) {
            throw new LiveBroadcastInputException(sprintf('Could not connect to port 1935 of at %s', $inputStream));
        }

        return sprintf('-re -i rtmp://%s', escapeshellarg($inputStream));
    }

    /**
     * @param AbstractMedia $media
     *
     * @return InputRtmp
     */
    public function setMedia(AbstractMedia $media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Returns the media type
     *
     * @return string
     */
    public function getMediaType()
    {
        return MediaRtmp::class;
    }
}
