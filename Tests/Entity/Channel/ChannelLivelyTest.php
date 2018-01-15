<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelLively;

/**
 * Class ChannelLivelyTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Channel
 */
class ChannelLivelyTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Test the getters and setters
     */
    public function testGetMethods()
    {
        $channel = new ChannelLively();
        $channel->setChannelName('Live.ly');
        $channel->setStreamKey('secret');
        $channel->setStreamServer('server');

        self::assertEquals('Live.ly', $channel->getChannelName());
        self::assertEquals('secret', $channel->getStreamKey());
        self::assertEquals('server', $channel->getStreamServer());
    }

    /**
     * Test the __toString method
     */
    public function testToString()
    {
        $channel = new ChannelLively();
        self::assertEquals('Live.ly: ', (string) $channel);

        $channel->setChannelName('channelName');
        self::assertEquals('Live.ly: channelName', (string) $channel);
    }

    /**
     * Test the isEntityConfigured method
     */
    public function testIsEntityConfigured()
    {
        $channel = new ChannelLively();
        $configuration = array();

        self::assertTrue($channel::isEntityConfigured($configuration));
    }
}
