<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Output;

/**
 * Class Facebook
 * @package Martin1982\LiveBroadcastBundle\Streams\Output
 */
class Facebook implements OutputInterface
{
    const CHANNEL_NAME = 'facebook';

    /**
     * Facebook constructor
     */
    public function __construct()
    {
        throw new \Exception('Facebook support is still pending...');
    }

    /**
     * Give the cmd string to start the stream
     *
     * @return string
     */
    public function generateOutputCmd()
    {
        // TODO: Implement generateOutputCmd() method.
    }
}
