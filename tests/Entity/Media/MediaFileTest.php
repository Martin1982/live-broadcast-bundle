<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use PHPUnit\Framework\TestCase;

/**
 * Class MediaFileTest
 */
class MediaFileTest extends TestCase
{
    /**
     *
     */
    public function testAbstractMediaProperties(): void
    {
        $input = new MediaFile();
        self::assertNull($input->getId());
        self::assertEquals('', (string) $input);
    }

    /**
     * Test the getFileLocation method
     */
    public function testFileLocation(): void
    {
        $input = new MediaFile();
        self::assertEquals('', $input->getFileLocation());

        $input->setFileLocation('/tmp/file/location');
        self::assertEquals('/tmp/file/location', $input->getFileLocation());

        $input->setFileLocation('/tmp/test');
        self::assertEquals('/tmp/test', $input->getFileLocation());
    }

    /**
     * Test the __toString method
     */
    public function testToString(): void
    {
        $input = new MediaFile();
        self::assertEquals('', (string) $input);

        $input->setFileLocation('/tmp/test');
        self::assertEquals('/tmp/test', (string) $input);
    }
}
