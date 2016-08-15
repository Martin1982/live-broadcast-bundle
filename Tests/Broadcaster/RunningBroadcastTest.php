<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class RunningBroadcastTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Broadcaster
 */
class RunningBroadcastTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get method.
     */
    public function testGetMethods()
    {
        $running = new RunningBroadcast(1, 2, 44, 'test');
        self::assertEquals(1, $running->getBroadcastId());
        self::assertEquals(2, $running->getProcessId());
        self::assertEquals(44, $running->getChannelId());
        self::assertEquals('test', $running->getEnvironment());
    }

    /**
     * Test the isValid method.
     */
    public function testIsValid()
    {
        $running = new RunningBroadcast(null, null, null, null);
        self::assertFalse($running->isValid(''));

        $running = new RunningBroadcast(1, null, null, null);
        self::assertFalse($running->isValid(''));

        $running = new RunningBroadcast(null, 2, null, null);
        self::assertFalse($running->isValid(''));

        $running = new RunningBroadcast(null, 2, 3, null);
        self::assertFalse($running->isValid(''));

        $running = new RunningBroadcast(1, 2, null, null);
        self::assertFalse($running->isValid(''));

        $running = new RunningBroadcast(1, 2, 3, 'unit');
        self::assertFalse($running->isValid('test'));

        $running = new RunningBroadcast(1, 2, 3, 'test');
        self::assertTrue($running->isValid('test'));
    }

    /**
     * Test the isBroadcasting method
     */
    public function testIsBroadcasting()
    {
        /* Create a running broadcast with string values as id's */
        $running = new RunningBroadcast('5', '2', '6', 'test');

        $liveBroadcast = $this->getLiveBroadcast(5);
        $channel = $this->getChannelTwitch(6);

        self::assertTrue($running->isBroadcasting($liveBroadcast, $channel));

        $liveBroadcast = $this->getLiveBroadcast(7);
        self::assertFalse($running->isBroadcasting($liveBroadcast, $channel));

        $liveBroadcast = $this->getLiveBroadcast(5);
        $channel = $this->getChannelTwitch(8);
        self::assertFalse($running->isBroadcasting($liveBroadcast, $channel));

        $running = new RunningBroadcast(5, 2, 8, 'test');
        self::assertTrue($running->isBroadcasting($liveBroadcast, $channel));
    }

    /**
     * @param int $id
     * @return ChannelTwitch
     */
    private function getChannelTwitch($id)
    {
        $channel = new ChannelTwitch();
        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('channelId');
        $property->setAccessible(true);
        $property->setValue($channel, $id);

        return $channel;
    }

    /**
     * @param int $id
     * @return LiveBroadcast
     */
    private function getLiveBroadcast($id)
    {
        $liveBroadcast = new LiveBroadcast();
        $reflection = new \ReflectionClass($liveBroadcast);
        $property = $reflection->getProperty('broadcastId');
        $property->setAccessible(true);
        $property->setValue($liveBroadcast, $id);

        return $liveBroadcast;
    }
}
