<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Streams\Input;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputFile;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Streams\Input\File;
use Martin1982\LiveBroadcastBundle\Streams\InputFactory;

/**
 * Class FileTest.
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if the File input class implements the correct interface.
     */
    public function testFileInterface()
    {
        $implements = class_implements('Martin1982\LiveBroadcastBundle\Streams\Input\File');
        self::assertEquals(count($implements),  1);
        self::assertTrue(in_array('Martin1982\LiveBroadcastBundle\Streams\Input\InputInterface', $implements));
    }

    /**
     * @expectedException Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testFileNotExists()
    {
        $input = new InputFile();
        $input->setFileLocation('/not-really-there');

        $broadcast = new LiveBroadcast();
        $broadcast->setInput($input);

        new File($broadcast);
    }

    /**
     * Test if the File input class generates the correct input command.
     */
    public function testGenerateInputCmd()
    {
        $fileName = '/tmp/videoFile.txt';
        fopen($fileName, 'w');

        $input = new InputFile();
        $input->setFileLocation($fileName);

        $broadcast = new LiveBroadcast();
        $broadcast->setInput($input);

        $inputFile = new File($broadcast);

        self::assertEquals($inputFile->generateInputCmd(), '-re -i '.$fileName.' -vcodec copy -acodec copy');

        unlink($fileName);
    }

    /**
     * Test the output of the factory.
     */
    public function testFileInputFactory()
    {
        $fileName = '/tmp/factoryFile.txt';
        fopen($fileName, 'w');

        $input = new InputFile();
        $input->setFileLocation($fileName);

        $broadcast = new LiveBroadcast();
        $broadcast->setInput($input);

        $inputFactoryFile = InputFactory::loadInputStream($broadcast);
        self::assertEquals('Martin1982\LiveBroadcastBundle\Streams\Input\File', get_class($inputFactoryFile));

        unlink($fileName);
    }
}
