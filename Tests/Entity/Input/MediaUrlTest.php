<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\InputInterface;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaUrl;

/**
 * Class MediaUrlTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Media
 */
class MediaUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if the Url input class implements the correct interface.
     */
    public function testUrlInterface()
    {
        $implements = class_implements(MediaUrl::class);
        self::assertTrue(in_array(InputInterface::class, $implements));
    }

    /**
     * Test the getUrl method
     */
    public function testGetUrl()
    {
        $input = new MediaUrl();
        self::assertEquals('', $input->getUrl());

        $input->setUrl('http://www.google.com');
        self::assertEquals('http://www.google.com', $input->getUrl());
    }

    /**
     * Test the __toString method
     */
    public function testToString()
    {
        $input = new MediaUrl();
        self::assertEquals('', (string) $input);

        $input->setUrl('https://github.com/Martin1982/live-broadcast-bundle');
        self::assertEquals('https://github.com/Martin1982/live-broadcast-bundle', $input->getUrl());
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testInvalidUrl()
    {
        $input = new MediaUrl();
        $input->setUrl('http://w&w&w.invalid.url');
        $input->generateInputCmd();
    }

    /**
     * Test if the input command is correct
     */
    public function testGenerateInputCmd()
    {
        $url = 'https://www.video.com/test.mp4';
        $input = new MediaUrl();
        $input->setUrl($url);

        self::assertEquals(
            $input->generateInputCmd(),
            '-re -i ' . escapeshellarg($url)
        );
    }
}
