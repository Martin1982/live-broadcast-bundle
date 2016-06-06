<?php

namespace Martin1982\LiveBroadcastBundle\Streams;
use Martin1982\LiveBroadcastBundle\Streams\Output\OutputInterface;

/**
 * Class ChannelFactory
 *
 * This class maps the Channel classes to their
 * respective Output classes.
 *
 * To add custom channels and outputs extend this
 * class and add it to the $mappings property, or
 * overwrite the mapping completely.
 *
 * @package Martin1982\LiveBroadcastBundle\Streams
 */
class OutputFactory
{

    const CHANNEL_FACEBOOK  = 'Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook';
    const CHANNEL_TWITCH    = 'Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch';
    const CHANNEL_YOUTUBE   = 'Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube';

    const OUTPUT_FACEBOOK   = 'Martin1982\LiveBroadcastBundle\Streams\Output\Facebook';
    const OUTPUT_TWITCH     = 'Martin1982\LiveBroadcastBundle\Streams\Output\Twitch';
    const OUTPUT_YOUTUBE    = 'Martin1982\LiveBroadcastBundle\Streams\Output\Youtube';

    /**
     * @var array
     */
    public static $mapping = array(
        self::CHANNEL_FACEBOOK  => self::OUTPUT_FACEBOOK,
        self::CHANNEL_TWITCH    => self::OUTPUT_TWITCH,
        self::CHANNEL_YOUTUBE   => self::OUTPUT_YOUTUBE,
    );

    /**
     * Get the output class for the given channel
     *
     * @param $channel
     * @return OutputInterface
     */
    public static function loadOutput($channel)
    {
        $reflection = new \ReflectionClass($channel);
        $channelClassName = $reflection->getName();

        if (array_key_exists($channelClassName, self::$mapping)) {
            return new self::$mapping[$channelClassName]($channel);
        }
    }

}
