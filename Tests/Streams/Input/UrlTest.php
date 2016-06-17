<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Streams\Input;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputUrl;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Streams\Input\Url;
use Martin1982\LiveBroadcastBundle\Streams\InputFactory;

/**
 * Class UrlTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Streams\Input
 */
class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if the Url input class implements the correct interface.
     */
    public function testUrlInterface()
    {
        $implements = class_implements('Martin1982\LiveBroadcastBundle\Streams\Input\Url');
        self::assertEquals(count($implements),  1);
        self::assertTrue(in_array('Martin1982\LiveBroadcastBundle\Streams\Input\InputInterface', $implements));
    }

    /**
     * @expectedException Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testInvalidUrl()
    {
        $input = new InputUrl();
        $input->setUrl('http://w&w&w.invalid.url');

        $broadcast = new LiveBroadcast();
        $broadcast->setInput($input);

        new Url($broadcast);
    }

    /**
     * Test if the Url input class generates the correct input command.
     */
    public function testGenerateInputCmd()
    {
        $url = 'https://www.video.com/test.mp4';
        $input = new InputUrl();
        $input->setUrl($url);

        $broadcast = new LiveBroadcast();
        $broadcast->setInput($input);

        $inputUrl = new Url($broadcast);

        self::assertEquals($inputUrl->generateInputCmd(), '-re -i "' . $url . '"');
    }

    /**
     * Test the output of the factory
     */
    public function testUrlInputFactory()
    {
        $url = 'https://www.video.com/factory.mp4';
        $input = new InputUrl();
        $input->setUrl($url);

        $broadcast = new LiveBroadcast();
        $broadcast->setInput($input);

        $inputFactoryUrl = InputFactory::loadInputStream($broadcast);
        self::assertEquals('Martin1982\LiveBroadcastBundle\Streams\Input\Url', get_class($inputFactoryUrl));
    }
}
