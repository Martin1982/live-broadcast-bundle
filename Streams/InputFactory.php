<?php

namespace Martin1982\LiveBroadcastBundle\Streams;

use Martin1982\LiveBroadcastBundle\Entity\Input\BaseInput;
use Martin1982\LiveBroadcastBundle\Streams\Input\InputInterface;

/**
 * Class InputFactory
 *
 * This class maps the Input entity classes to their
 * respective Input stream classes.
 *
 * To add custom entities and input streams extend this
 * class and add it to the $mappings property, or
 * overwrite the mapping completely.
 *
 * @package Martin1982\LiveBroadcastBundle\Streams
 */
class InputFactory
{

    const INPUT_FILE        = 'Martin1982\LiveBroadcastBundle\Entity\Input\File';
    const INPUT_URL         = 'Martin1982\LiveBroadcastBundle\Entity\Input\Url';
    const INPUT_RTMP        = 'Martin1982\LiveBroadcastBundle\Entity\Input\Rtmp';
    const INPUT_SPOTIFY     = 'Martin1982\LiveBroadcastBundle\Entity\Input\Spotify';

    const INPUT_STREAM_FILE     = 'Martin1982\LiveBroadcastBundle\Streams\Input\File';
    const INPUT_STREAM_URL      = 'Martin1982\LiveBroadcastBundle\Streams\Input\Url';
    const INPUT_STREAM_RTMP     = 'Martin1982\LiveBroadcastBundle\Streams\Input\Rtmp';
    const INPUT_STREAM_SPOTIFY  = 'Martin1982\LiveBroadcastBundle\Streams\Input\Spotify';

    /**
     * @var array
     */
    public static $mapping = array(
        self::INPUT_FILE    => self::INPUT_STREAM_FILE,
        self::INPUT_URL     => self::INPUT_STREAM_URL,
        self::INPUT_RTMP    => self::INPUT_STREAM_RTMP,
        self::INPUT_SPOTIFY => self::INPUT_STREAM_SPOTIFY,
    );

    /**
     * Get the input stream class for the given entity
     *
     * @param BaseInput $input
     * @return InputInterface
     */
    public static function loadInputStream(BaseInput $input)
    {
        $reflection = new \ReflectionClass($input);
        $inputClassName = $reflection->getName();

        if (array_key_exists($inputClassName, self::$mapping)) {
            return new self::$mapping[$inputClassName]($input);
        }

        return;
    }

}
