<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use PHPUnit\Framework\TestCase;

/**
 * Class ChannelYouTubeTest
 */
class ChannelYouTubeTest extends TestCase
{
    /**
     * Test the getters and setters
     */
    public function testGetMethods()
    {
        $channel = new ChannelYouTube();
        $channel->setChannelName('YouTube')->setRefreshToken('refresh')->setYouTubeChannelName('YT-Name');

        self::assertEquals('YouTube', $channel->getChannelName());
        self::assertEquals('refresh', $channel->getRefreshToken());
        self::assertEquals('YT-Name', $channel->getYouTubeChannelName());
    }

    /**
     * Test the __toString method
     */
    public function testToString()
    {
        $channel = new ChannelYouTube();
        self::assertEquals('YouTube: ', (string) $channel);

        $channel->setChannelName('channelName');
        self::assertEquals('YouTube: channelName', (string) $channel);
    }

    /**
     * Test the isEntityConfigured method
     */
    public function testIsEntityConfigured()
    {
        $channel = new ChannelYouTube();
        $configuration = [];

        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['youtube'] = [];
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['youtube']['client_id'] = null;
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['youtube']['client_secret'] = null;
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['youtube']['redirect_route'] = null;
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['youtube']['client_id'] = 'id';
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['youtube']['client_secret'] = 'secret';
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['youtube']['redirect_route'] = 'route';
        self::assertTrue($channel::isEntityConfigured($configuration));
    }
}
