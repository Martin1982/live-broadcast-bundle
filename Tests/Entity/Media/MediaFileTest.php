<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use PHPUnit\Framework\TestCase;

/**
 * Class MediaFileTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Media
 */
class MediaFileTest extends TestCase
{
    /**
     *
     */
    public function testBaseMediaProperties()
    {
        $input = new MediaFile();
        self::assertNull($input->getId());
        self::assertEquals('', (string) $input);
    }

    /**
     * Test the getFileLocation method
     */
    public function testFileLocation()
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
    public function testToString()
    {
        $input = new MediaFile();
        self::assertEquals('', (string) $input);

        $input->setFileLocation('/tmp/test');
        self::assertEquals('/tmp/test', (string) $input);
    }
}
