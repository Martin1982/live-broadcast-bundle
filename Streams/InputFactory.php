<?php

namespace Martin1982\LiveBroadcastBundle\Streams;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Streams\Input\InputInterface;

/**
 * Class InputFactory.
 *
 * This class maps the Input entity classes to their
 * respective Input stream classes.
 *
 * To add custom entities and input streams extend this
 * class and add it to the $mappings property, or
 * overwrite the mapping completely.
 */
class InputFactory
{
    const INPUT_FILE = 'Martin1982\LiveBroadcastBundle\Entity\Input\InputFile';
    const INPUT_URL = 'Martin1982\LiveBroadcastBundle\Entity\Input\InputUrl';
    const INPUT_RTMP = 'Martin1982\LiveBroadcastBundle\Entity\Input\InputRtmp';

    const INPUT_STREAM_FILE = 'Martin1982\LiveBroadcastBundle\Streams\Input\File';
    const INPUT_STREAM_URL = 'Martin1982\LiveBroadcastBundle\Streams\Input\Url';
    const INPUT_STREAM_RTMP = 'Martin1982\LiveBroadcastBundle\Streams\Input\Rtmp';

    /**
     * @var array
     */
    public static $mapping = array(
        self::INPUT_FILE => self::INPUT_STREAM_FILE,
        self::INPUT_URL => self::INPUT_STREAM_URL,
        self::INPUT_RTMP => self::INPUT_STREAM_RTMP,
    );

    /**
     * Get the input stream class for the given entity.
     *
     * @param LiveBroadcast $broadcast
     *
     * @return InputInterface
     */
    public static function loadInputStream(LiveBroadcast $broadcast)
    {
        $input = $broadcast->getInput();

        $reflection = new \ReflectionClass($input);
        $inputClassName = $reflection->getName();

        if (array_key_exists($inputClassName, self::$mapping)) {
            return new self::$mapping[$inputClassName]($broadcast);
        }

        return;
    }
}
