<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;

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
}
