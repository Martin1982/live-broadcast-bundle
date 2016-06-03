<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Input;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class Rtmp
 * @package Martin1982\LiveBroadcastBundle\Streams\Input
 */
class Rtmp implements InputInterface
{
    const INPUT_TYPE = 'rtmp';

    /**
     * Rtmp constructor
     */
    public function __construct(LiveBroadcast $broadcast)
    {
        throw new \Exception('Rtmp support is still pending...');
    }

    /**
     * @return string
     */
    public function generateInputCmd()
    {
        // TODO: Implement generateInputCmd() method.
    }
}
