<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaMonitorStream;
use PHPUnit\Framework\TestCase;

/**
 * Class MediaMonitorStreamTest
 */
class MediaMonitorStreamTest extends TestCase
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
