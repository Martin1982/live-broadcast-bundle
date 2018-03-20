<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Channel;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelLively;
use PHPUnit\Framework\TestCase;

/**
 * Class ChannelLivelyTest
 */
class ChannelLivelyTest extends TestCase
{
    /**
     * Test the getters and setters
     */
    public function testGetMethods(): void
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
    public function testToString(): void
    {
        $channel = new ChannelLively();
        self::assertEquals('Live.ly: ', (string) $channel);

        $channel->setChannelName('channelName');
        self::assertEquals('Live.ly: channelName', (string) $channel);
    }

    /**
     * Test the isEntityConfigured method
     */
    public function testIsEntityConfigured(): void
    {
        $channel = new ChannelLively();
        $configuration = [];

        self::assertTrue($channel::isEntityConfigured($configuration));
    }
}
