<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Input;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputInterface;
use Martin1982\LiveBroadcastBundle\Entity\Input\InputUrl;

/**
 * Class InputUrlTest.
 */
class InputUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if the Url input class implements the correct interface.
     */
    public function testUrlInterface()
    {
        $implements = class_implements(InputUrl::class);
        self::assertTrue(in_array(InputInterface::class, $implements));
    }

    /**
     * Test the getUrl method
     */
    public function testGetUrl()
    {
        $input = new InputUrl();
        self::assertEquals('', $input->getUrl());

        $input->setUrl('http://www.google.com');
        self::assertEquals('http://www.google.com', $input->getUrl());
    }

    /**
     * Test the __toString method
     */
    public function testToString()
    {
        $input = new InputUrl();
        self::assertEquals('', (string) $input);

        $input->setUrl('https://github.com/Martin1982/live-broadcast-bundle');
        self::assertEquals('https://github.com/Martin1982/live-broadcast-bundle', $input->getUrl());
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testInvalidUrl()
    {
        $input = new InputUrl();
        $input->setUrl('http://w&w&w.invalid.url');
        $input->generateInputCmd();
    }

    /**
     * Test if the input command is correct
     */
    public function testGenerateInputCmd()
    {
        $url = 'https://www.video.com/test.mp4';
        $input = new InputUrl();
        $input->setUrl($url);

        self::assertEquals(
            $input->generateInputCmd(),
            '-re -i ' . escapeshellarg($url)
        );
    }
}
