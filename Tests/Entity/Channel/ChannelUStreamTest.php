<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelUstream;
use PHPUnit\Framework\TestCase;

/**
 * Class ChannelUStreamTest
 */
class ChannelUStreamTest extends TestCase
{
    /**
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
        $configuration = [];

        self::assertTrue($channel::isEntityConfigured($configuration));
    }
}
