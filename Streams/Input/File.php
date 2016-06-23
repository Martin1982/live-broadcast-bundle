<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Input;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputFile;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class File.
 */
class File implements InputInterface
{
    const INPUT_TYPE = 'file';

    /** @var  LiveBroadcast */
    protected $broadcast;

    /**
     * File constructor.
     *
     * @param LiveBroadcast $broadcast
     *
     * @throws LiveBroadcastException
     */
    public function __construct(LiveBroadcast $broadcast)
    {
        /** @var InputFile $inputEntity */
        $inputEntity = $broadcast->getInput();
        $inputFilename = $inputEntity->getFileLocation();

        if (!file_exists($inputFilename)) {
            throw new LiveBroadcastException(sprintf('Could not find input file %s', $inputFilename));
        }

        $this->broadcast = $broadcast;
    }

    /**
     * Get the input command part.
     *
     * @return string
     */
    public function generateInputCmd()
    {
        /** @var InputFile $inputEntity */
        $inputEntity = $this->broadcast->getInput();
        $inputFilename = $inputEntity->getFileLocation();

        return sprintf('-re -i %s', escapeshellarg($inputFilename));
    }
}
