<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;

/**
 * Class ChannelFacebooKTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Channel
 */
class ChannelFacebookTest extends \PHPUnit_Framework_TestCase
{
    /*
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
        $configuration = array();

        self::assertFalse($channel::isEntityConfigured($configuration));

        $configuration['facebook'] = array();
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
