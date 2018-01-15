<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;

/**
 * Class ChannelTwitchTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Channel
 */
class ChannelTwitchTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Test the getters and setters
     */
    public function testGetMethods()
    {
        $channel = new ChannelTwitch();
        self::assertEquals('live.twitch.tv', $channel->getStreamServer());

        $channel->setChannelName('UnitTest')->setStreamKey('key')->setStreamServer('server');
        self::assertEquals('UnitTest', $channel->getChannelName());
        self::assertEquals('key', $channel->getStreamKey());
        self::assertEquals('server', $channel->getStreamServer());
    }

    /**
     * Test the __toString method
     */
    public function testToString()
    {
        $channel = new ChannelTwitch();
        self::assertEquals('Twitch: ', (string) $channel);

        $channel->setChannelName('TwitchTest');
        self::assertEquals('Twitch: TwitchTest', (string) $channel);
    }

    /**
     * Test the isEntityConfigured method
     */
    public function testIsEntityConfigured()
    {
        $channel = new ChannelTwitch();
        $configuration = array();

        self::assertTrue($channel::isEntityConfigured($configuration));
    }
}
