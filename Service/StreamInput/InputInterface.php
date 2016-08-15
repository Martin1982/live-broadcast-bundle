<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\BaseMedia;

/**
 * Interface InputInterface
 * @package Martin1982\LiveBroadcastBundle\Service\StreamInput
 */
interface InputInterface
{
    /**
     * @return string
     */
    public function generateInputCmd();

    /**
     * @param BaseMedia $media
     * @return mixed
     */
    public function setMedia(BaseMedia $media);

    /**
     * Returns the media type
     *
     * @return BaseMedia
     */
    public function getMediaType();
}
