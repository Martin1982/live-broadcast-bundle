<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Input;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

class InputMonitorStream extends BaseInput
{
    /**
     * @var string
     */
    protected $monitorImage;

    /**
     * @return mixed
     */
    public function getMonitorImage()
    {
        return $this->monitorImage;
    }

    /**
     * @param string $monitorImage
     * @return InputMonitorStream
     * @throws LiveBroadcastException
     */
    public function setMonitorImage($monitorImage)
    {
        if (!file_exists($monitorImage)) {
            throw new LiveBroadcastException(sprintf('Monitor image \'%s\' not found', $monitorImage));
        }
        $this->monitorImage = $monitorImage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function generateInputCmd()
    {
        return sprintf(
            '-re -f lavfi -i anullsrc=r=48000 -r 1 -loop 1 -i %s',
            escapeshellarg($this->getMonitorImage())
        );
    }
}
