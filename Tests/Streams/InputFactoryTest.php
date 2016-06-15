<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Streams;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputFile;
use Martin1982\LiveBroadcastBundle\Entity\Input\InputUrl;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Streams\InputFactory;

/**
 * Class InputFactoryTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Streams
 */
class InputFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test an unknown input for a broadcast
     */
    public function testUnknownInputFactory()
    {
        $input = new InvalidInput();
        $broadcast = new LiveBroadcast();
        $broadcast->setInput($input);

        $inputFactory = InputFactory::loadInputStream($broadcast);

        self::assertEquals(null, $inputFactory);
    }

    /**
     * Test input factory with file
     */
    public function testFileInputFactory()
    {
        $fileName = '/tmp/inputfile.txt';
        fopen($fileName, 'w');

        $input = new InputFile();
        $input->setFileLocation($fileName);

        $broadcast = new LiveBroadcast();
        $broadcast->setInput($input);

        $inputFactory = InputFactory::loadInputStream($broadcast);

        self::assertInstanceOf('Martin1982\LiveBroadcastBundle\Streams\Input\File', $inputFactory);

        unlink($fileName);
    }

    /**
     * Test input factory with URL
     */
    public function testUrlInputFactory()
    {
        $input = new InputUrl();
        $input->setUrl('http://www.google.com/testvideo.mp4');

        $broadcast = new LiveBroadcast();
        $broadcast->setInput($input);

        $inputFactory = InputFactory::loadInputStream($broadcast);

        self::assertInstanceOf('Martin1982\LiveBroadcastBundle\Streams\Input\Url', $inputFactory);
    }
}
