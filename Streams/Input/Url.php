<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Input;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputUrl;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class Url
 * @package Martin1982\LiveBroadcastBundle\Streams\Input
 */
class Url implements InputInterface
{
    const INPUT_TYPE = 'url';

    /** @var  LiveBroadcast */
    protected $broadcast;

    /**
     * Url constructor.
     *
     * @param LiveBroadcast $broadcast
     *
     * @throws LiveBroadcastException
     */
    public function __construct(LiveBroadcast $broadcast)
    {
        /** @var InputUrl $inputEntity */
        $inputEntity = $broadcast->getInput();
        $inputUrl = $inputEntity->getUrl();

        if (filter_var($inputUrl, FILTER_VALIDATE_URL) === false) {
            throw new LiveBroadcastException(sprintf('Invalid URL %s', $inputUrl));
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
        /** @var InputUrl $inputEntity */
        $inputEntity = $this->broadcast->getInput();
        $inputUrl = $inputEntity->getUrl();

        return sprintf('-re -i %s -vcodec copy -acodec copy', escapeshellarg($inputUrl));
    }
}
