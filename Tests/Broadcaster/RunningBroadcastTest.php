<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;

/**
 * Class RunningBroadcastTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Broadcaster
 */
class RunningBroadcastTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get method
     */
    public function testGetMethods()
    {
        $running = new RunningBroadcast(1, 2);
        $this->assertEquals($running->getBroadcastId(), 1);
        $this->assertEquals($running->getProcessId(), 2);
    }

    /**
     * Test the isValid method
     */
    public function testIsValid()
    {
        $running = new RunningBroadcast(null, null);
        $this->assertEquals($running->isValid(), false);

        $running = new RunningBroadcast(1, null);
        $this->assertEquals($running->isValid(), false);

        $running = new RunningBroadcast(null, 2);
        $this->assertEquals($running->isValid(), false);

        $running = new RunningBroadcast(1, 2);
        $this->assertEquals($running->isValid(), true);
    }
}