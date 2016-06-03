<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Output;

/**
 * Class Youtube
 * @package Martin1982\LiveBroadcastBundle\Streams\Output
 */
class Youtube implements OutputInterface
{
    const CHANNEL_NAME = 'youtube';

    /**
     * Youtube constructor.
     */
    public function __construct()
    {
        throw new \Exception('Youtube support is still pending...');
    }

    /**
     * Give the cmd string to start the stream.
     *
     * @return string
     */
    public function generateOutputCmd()
    {
        // TODO: Implement generateOutputCmd() method.
    }
}
