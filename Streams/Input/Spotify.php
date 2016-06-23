<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Input;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class Spotify.
 */
class Spotify implements InputInterface
{
    const INPUT_TYPE = 'spotify';

    /**
     * Spotify constructor.
     */
    public function __construct(LiveBroadcast $broadcast)
    {
        throw new \Exception('Spotify support is still pending...');
    }

    /**
     * @return string
     */
    public function generateInputCmd()
    {
        // TODO: Implement generateInputCmd() method.
    }
}
