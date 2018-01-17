<?php

namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Entity\Media\BaseMedia;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputInterface;

/**
 * Class StreamInputService
 * @package Martin1982\LiveBroadcastBundle\Service
 */
class StreamInputService
{
    /**
     * @var InputInterface[]
     */
    private $streamInputs = [];

    /**
     * @param InputInterface $streamInput
     * @param string $media
     */
    public function addStreamInput(InputInterface $streamInput, $media)
    {
        $this->streamInputs[$media] = $streamInput;
    }

    /**
     * @param BaseMedia $media
     * @return InputInterface
     * @throws LiveBroadcastInputException
     */
    public function getInputInterface(BaseMedia $media)
    {
        foreach ($this->streamInputs as $streamInput) {
            if ($streamInput->getMediaType() === get_class($media)) {
                $streamInput->setMedia($media);

                return $streamInput;
            }
        }

        throw new LiveBroadcastInputException('No InputInterface configured for media '.$media);
    }
}
