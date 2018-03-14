<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use PHPUnit\Framework\TestCase;

/**
 * Class ChannelFacebooKTest
 */
class ChannelFacebookTest extends TestCase
{
    /**
     * Test the getters and setters
     */
    public function testGetMethods()
    {
        $channel = new ChannelFacebook();
        $channel->setChannelName('Testing123')->setAccessToken('456789')->setFbEntityId('Facebook');

        self::assertEquals('Testing123', $channel->getChannelName());
        self::assertEquals('456789', $channel->getAccessToken());
        self::assertEquals('Facebook', $channel->getFbEntityId());
    }

    /**
     * Test the __toString method
     */
    public function testToString()
    {
        $channel = new ChannelFacebook();
        self::assertEquals('Facebook: ', (string) $channel);

        $channel->setChannelName('UnitTest');
        self::assertEquals('Facebook: UnitTest', (string) $channel);
    }

    /**
     * Test the isEntityConfigured method
     */
    public function testIsEntityConfigured()
    {
        $channel = new ChannelFacebook();
        $configuration = [];

        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['facebook'] = [];
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['facebook']['application_id'] = null;
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['facebook']['application_secret'] = null;
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['facebook']['application_id'] = 'ID';
        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['facebook']['application_secret'] = 'SECRET';
        self::assertTrue($channel::isEntityConfigured($configuration));
    }
}
