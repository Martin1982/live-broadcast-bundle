<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\BaseMedia;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaMonitorStream;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;

/**
 * Class InputMonitorStream
 * @package Martin1982\LiveBroadcastBundle\Service\StreamInput
 */
class InputMonitorStream implements InputInterface
{
    /**
     * @var MediaMonitorStream
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
     * {@inheritdoc}
     * @throws LiveBroadcastInputException
     */
    public function generateInputCmd()
    {
        $monitorImage = $this->media->getMonitorImage();

        if (!file_exists($monitorImage)) {
            throw new LiveBroadcastInputException(sprintf('Monitor image \'%s\' not found', $monitorImage));
        }

        return sprintf(
            '-re -f lavfi -i anullsrc=r=48000 -r 1 -loop 1 -i %s',
            escapeshellarg($monitorImage)
        );
    }

    /**
     * @return MediaMonitorStream
     */
    public function getMediaType()
    {
        return MediaMonitorStream::class;
    }
}
