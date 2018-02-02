<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\BaseMedia;
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
     * @param BaseMedia $media
     *
     * @return InputRtmp
     */
    public function setMedia(BaseMedia $media)
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
