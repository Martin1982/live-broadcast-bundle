<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;

/**
 * Class ChannelYouTubeTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Channel
 */
class ChannelYouTubeTest extends \PHPUnit_Framework_TestCase
{
    /*
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
        $configuration = array();

        self::assertFalse($channel->isEntityConfigured($configuration));

        $configuration['youtube'] = array();
        self::assertFalse($channel->isEntityConfigured($configuration));

        $configuration['youtube']['client_id'] = null;
        self::assertFalse($channel->isEntityConfigured($configuration));

        $configuration['youtube']['client_secret'] = null;
        self::assertFalse($channel->isEntityConfigured($configuration));

        $configuration['youtube']['redirect_route'] = null;
        self::assertFalse($channel->isEntityConfigured($configuration));

        $configuration['youtube']['client_id'] = 'id';
        self::assertFalse($channel->isEntityConfigured($configuration));

        $configuration['youtube']['client_secret'] = 'secret';
        self::assertFalse($channel->isEntityConfigured($configuration));

        $configuration['youtube']['redirect_route'] = 'route';
        self::assertTrue($channel->isEntityConfigured($configuration));
    }
}
