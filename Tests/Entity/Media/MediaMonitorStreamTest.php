<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
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
