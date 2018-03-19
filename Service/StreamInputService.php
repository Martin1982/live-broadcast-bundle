<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputInterface;

/**
 * Class StreamInputService
 */
class StreamInputService
{
    /**
     * @var InputInterface[]
     */
    private $streamInputs = [];

    /**
     * @param InputInterface $streamInput
     * @param string         $media
     */
    public function addStreamInput(InputInterface $streamInput, $media): void
    {
        $this->streamInputs[$media] = $streamInput;
    }

    /**
     * @param AbstractMedia $media
     *
     * @return InputInterface
     *
     * @throws LiveBroadcastInputException
     */
    public function getInputInterface(AbstractMedia $media): InputInterface
    {
        foreach ($this->streamInputs as $streamInput) {
            $mediaType = $streamInput->getMediaType();

            if ($media instanceof $mediaType) {
                $streamInput->setMedia($media);

                return $streamInput;
            }
        }

        throw new LiveBroadcastInputException(sprintf('No InputInterface configured for media %s', $media));
    }
}
