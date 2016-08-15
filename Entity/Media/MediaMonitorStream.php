<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Media;

/**
 * Class MediaMonitorStream
 * @package Martin1982\LiveBroadcastBundle\Entity\Media
 */
class MediaMonitorStream extends BaseMedia
{
    /**
     * @var string
     */
    protected $monitorImage;

    /**
     * @return string
     */
    public function getMonitorImage()
    {
        return $this->monitorImage;
    }

    /**
     * @param string $monitorImage
     * @return MediaMonitorStream
     */
    public function setMonitorImage($monitorImage)
    {
        $this->monitorImage = $monitorImage;

        return $this;
    }

    public function __toString()
    {
        return $this->getMonitorImage();
    }
}
