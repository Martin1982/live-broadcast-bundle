<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaRtmp;
use PHPUnit\Framework\TestCase;

/**
 * Class MediaRtmpTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Media
 */
class MediaRtmpTest extends TestCase
{
    /**
     * Test the basic properties
     */
    public function testBaseMediaProperties()
    {
        $input = new MediaRtmp();
        self::assertNull($input->getId());
        self::assertEquals('', (string) $input);
    }

    /**
     * Test the getRtmpAddress method
     */
    public function testFileLocation()
    {
        $input = new MediaRtmp();
        self::assertEquals('', $input->getRtmpAddress());

        $input->setRtmpAddress('rtmp://127.0.0.1/live/mystream');
        self::assertEquals('127.0.0.1/live/mystream', $input->getRtmpAddress());

        $input->setRtmpAddress('10.0.0.1/live/second');
        self::assertEquals('10.0.0.1/live/second', $input->getRtmpAddress());
    }

    /**
     * Test the __toString method
     */
    public function testToString()
    {
        $input = new MediaRtmp();
        self::assertEquals('', (string) $input);

        $input->setRtmpAddress('10.0.0.10/live/key');
        self::assertEquals('10.0.0.10/live/key', (string) $input);
    }
}
