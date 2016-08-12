<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Input;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputFile;
use Martin1982\LiveBroadcastBundle\Entity\Input\InputInterface;

/**
 * Class InputFileTest.
 */
class InputFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if the File input class implements the correct interface.
     */
    public function testFileInterface()
    {
        $implements = class_implements(InputFile::class);
        self::assertTrue(in_array(InputInterface::class, $implements));
    }

    /**
     * Test the getFileLocation method
     */
    public function testFileLocation()
    {
        $input = new InputFile();
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
        $input = new InputFile();
        self::assertEquals('', (string) $input);

        $input->setFileLocation('/tmp/test');
        self::assertEquals('/tmp/test', (string) $input);
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testFileNotExists()
    {
        $input = new InputFile();
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

        $input = new InputFile();
        $input->setFileLocation($fileName);

        self::assertEquals(
            $input->generateInputCmd(),
            '-re -i ' . escapeshellarg($fileName)
        );

        unlink($fileName);
    }
}
