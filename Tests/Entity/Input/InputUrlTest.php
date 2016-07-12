<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Input;

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
        $implements = class_implements('Martin1982\LiveBroadcastBundle\Entity\Input\InputUrl');
        self::assertEquals(count($implements), 1);
        self::assertTrue(in_array('Martin1982\LiveBroadcastBundle\Entity\Input\InputInterface', $implements));
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
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
