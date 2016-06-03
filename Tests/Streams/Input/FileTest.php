<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Streams\Input;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Streams\Input\File;

/**
 * Class FileTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Streams\Input
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if the File input class implements the correct interface.
     */
    public function testFileInterface()
    {
        $implements = class_implements('Martin1982\LiveBroadcastBundle\Streams\Input\File');
        $this->assertEquals(count($implements),  1);
        $this->assertTrue(in_array('Martin1982\LiveBroadcastBundle\Streams\Input\InputInterface', $implements));
    }

    /**
     * @expectedException Exception
     */
    public function testFileNotExists()
    {
        $broadcast = new LiveBroadcast();
        $broadcast->setVideoInputFile('/file/that/does/not/exist');

        new File($broadcast);
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidUrl()
    {
        $broadcast = new LiveBroadcast();
        $broadcast->setVideoInputFile('http://w&w&w.invalid.url');

        new File($broadcast);
    }

    /**
     * Test if the File input class generates the correct input command.
     */
    public function testGenerateInputCmd()
    {
        $fileName = 'videoFile.txt';
        fopen($fileName, 'w');

        $broadcast = new LiveBroadcast();
        $broadcast->setVideoInputFile('videoFile.txt');

        $inputFile = new File($broadcast);

        $this->assertEquals($inputFile->generateInputCmd(), '-re -i videoFile.txt -vcodec copy -acodec copy');

        unlink($fileName);
    }
}
