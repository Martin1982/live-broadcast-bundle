<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Input;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputFile;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class File
 * @package Martin1982\LiveBroadcastBundle\Streams\Input
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
     * @throws \Exception
     */
    public function __construct(LiveBroadcast $broadcast)
    {
        /** @var InputFile $inputEntity */
        $inputEntity = $broadcast->getInput();
        $inputFilename = $inputEntity->getFileLocation();

        // @Todo allow URL streams via a seperate input?
        if (!file_exists($inputFilename) && filter_var($inputFilename, FILTER_VALIDATE_URL) === false) {
            throw new \Exception(sprintf('Could not find input file %s', $inputFilename));
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

        return sprintf('-re -i %s -vcodec copy -acodec copy', $inputFilename);
    }
}
