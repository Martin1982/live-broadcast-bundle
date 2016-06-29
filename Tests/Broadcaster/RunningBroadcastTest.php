<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class RunningBroadcastTest.
 */
class RunningBroadcastTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get method.
     */
    public function testGetMethods()
    {
        $running = new RunningBroadcast(1, 2, 44);
        self::assertEquals($running->getBroadcastId(), 1);
        self::assertEquals($running->getProcessId(), 2);
        self::assertEquals($running->getChannelId(), 44);
    }

    /**
     * Test the isValid method.
     */
    public function testIsValid()
    {
        $running = new RunningBroadcast(null, null, null);
        self::assertEquals($running->isValid(), false);

        $running = new RunningBroadcast(1, null, null);
        self::assertEquals($running->isValid(), false);

        $running = new RunningBroadcast(null, 2, null);
        self::assertEquals($running->isValid(), false);

        $running = new RunningBroadcast(null, 2, 3);
        self::assertEquals($running->isValid(), false);

        $running = new RunningBroadcast(1, 2, null);
        self::assertEquals($running->isValid(), false);

        $running = new RunningBroadcast(1, 2, 3);
        self::assertEquals($running->isValid(), true);
    }

    /**
     * Test the isBroadcasting method
     */
    public function testIsBroadcasting()
    {
        /* Create a running broadcast with string values as id's */
        $running = new RunningBroadcast('5', '2', '6');

        $liveBroadcast = $this->getLiveBroadcast(5);
        $channel = $this->getChannelTwitch(6);

        self::assertEquals(true, $running->isBroadcasting($liveBroadcast, $channel));

        $liveBroadcast = $this->getLiveBroadcast(7);
        self::assertEquals(false, $running->isBroadcasting($liveBroadcast, $channel));

        $liveBroadcast = $this->getLiveBroadcast(5);
        $channel = $this->getChannelTwitch(8);
        self::assertEquals(false, $running->isBroadcasting($liveBroadcast, $channel));

        $running = new RunningBroadcast(5, 2, 8);
        self::assertEquals(true, $running->isBroadcasting($liveBroadcast, $channel));
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
