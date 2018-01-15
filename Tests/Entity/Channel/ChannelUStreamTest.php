<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelUstream;

/**
 * Class ChannelUStreamTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Channel
 */
class ChannelUStreamTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Test the getters and setters
     */
    public function testGetMethods()
    {
        $channel = new ChannelUstream();
        $channel->setChannelName('UStream')->setStreamKey('key')->setStreamServer('server');

        self::assertEquals('UStream', $channel->getChannelName());
        self::assertEquals('key', $channel->getStreamKey());
        self::assertEquals('server', $channel->getStreamServer());
    }

    /**
     * Test the __toString method
     */
    public function testToString()
    {
        $channel = new ChannelUstream();
        self::assertEquals('Ustream: ', (string) $channel);

        $channel->setChannelName('channelName');
        self::assertEquals('Ustream: channelName', (string) $channel);
    }

    /**
     * Test the isEntityConfigured method
     */
    public function testIsEntityConfigured()
    {
        $channel = new ChannelUstream();
        $configuration = array();

        self::assertTrue($channel::isEntityConfigured($configuration));
    }
}
