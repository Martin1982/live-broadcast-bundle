<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Input;

/**
 * Class Rtmp
 * @package Martin1982\LiveBroadcastBundle\Streams\Input
 */
class Rtmp
{
    const INPUT_TYPE = 'rtmp';

    /**
     * Rtmp constructor
     */
    public function __construct()
    {
        throw new \Exception('Rtmp support is still pending...');
    }
}