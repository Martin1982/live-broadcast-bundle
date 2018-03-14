<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use PHPUnit\Framework\TestCase;

/**
 * Class ChannelTwitchTest
 */
class ChannelTwitchTest extends TestCase
{
    /**
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
        $configuration = [];

        self::assertTrue($channel::isEntityConfigured($configuration));
    }
}
