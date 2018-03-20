<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaRtmp;
use PHPUnit\Framework\TestCase;

/**
 * Class MediaRtmpTest
 */
class MediaRtmpTest extends TestCase
{
    /**
     * Test the basic properties
     */
    public function testAbstractMediaProperties(): void
    {
        $input = new MediaRtmp();
        self::assertNull($input->getId());
        self::assertEquals('', (string) $input);
    }

    /**
     * Test the getRtmpAddress method
     */
    public function testFileLocation(): void
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
    public function testToString(): void
    {
        $input = new MediaRtmp();
        self::assertEquals('', (string) $input);

        $input->setRtmpAddress('10.0.0.10/live/key');
        self::assertEquals('10.0.0.10/live/key', (string) $input);
    }
}
