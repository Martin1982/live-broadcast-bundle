<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaMonitorStream;

/**
 * Class MediaMonitorStreamTest
 */
class MediaMonitorStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test monitor stream properties
     */
    public function testMonitorStreamProperties()
    {
        $monitor = new MediaMonitorStream();
        $monitor->setMonitorImage('abc');

        self::assertEquals('abc', $monitor->getMonitorImage());
        self::assertEquals('abc', (string) $monitor);
    }
}
