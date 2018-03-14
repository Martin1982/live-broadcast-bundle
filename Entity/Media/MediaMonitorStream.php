<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Media;

/**
 * Class MediaMonitorStream
 */
class MediaMonitorStream extends AbstractMedia
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
     *
     * @return MediaMonitorStream
     */
    public function setMonitorImage($monitorImage)
    {
        $this->monitorImage = $monitorImage;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getMonitorImage();
    }
}
