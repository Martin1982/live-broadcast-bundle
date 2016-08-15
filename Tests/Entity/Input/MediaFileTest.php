<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use Martin1982\LiveBroadcastBundle\Entity\Media\InputInterface;

/**
 * Class MediaFileTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Media
 */
class MediaFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if the File input class implements the correct interface.
     */
    public function testFileInterface()
    {
        $implements = class_implements(MediaFile::class);
        self::assertTrue(in_array(InputInterface::class, $implements));
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

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testFileNotExists()
    {
        $input = new MediaFile();
        $input->setFileLocation('/not-really-there');
        $input->generateInputCmd();
    }

    /**
     * Test if the input command is correct
     */
    public function testGenerateInputCmd()
    {
        $fileName = '/tmp/videoFile.txt';
        fopen($fileName, 'w');

        $input = new MediaFile();
        $input->setFileLocation($fileName);

        self::assertEquals(
            $input->generateInputCmd(),
            '-re -i ' . escapeshellarg($fileName)
        );

        unlink($fileName);
    }
}
