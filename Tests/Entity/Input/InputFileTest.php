<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Input;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputFile;

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
        $implements = class_implements('Martin1982\LiveBroadcastBundle\Entity\Input\InputFile');
        self::assertEquals(count($implements), 1);
        self::assertTrue(in_array('Martin1982\LiveBroadcastBundle\Entity\Input\InputInterface', $implements));
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
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
