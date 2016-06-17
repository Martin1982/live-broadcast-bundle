<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Streams\Output;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Streams\Output\Facebook;
use Martin1982\LiveBroadcastBundle\Streams\OutputFactory;

/**
 * Class FacebookTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Streams\Output
 */
class FacebookTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChannelFacebook
     */
    private $facebookChannel;

    /**
     * Setup a testable Facebook channel
     */
    public function setUp()
    {
        $this->facebookChannel = new ChannelFacebook();
        $this->facebookChannel->setAccessToken('token');
        $this->facebookChannel->setFbEntityId('id');
    }

    /**
     * Test if the Facebook output class implements the correct interface.
     */
    public function testFacebookConstructor()
    {
        $output = new Facebook($this->facebookChannel);
        self::assertInstanceOf('Martin1982\LiveBroadcastBundle\Streams\Output\Facebook', $output);
    }

    /**
     * Test the generate output command without a stream url set
     * @expectedException Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testInvalidGenerateOutputCmd()
    {
        $output = new Facebook($this->facebookChannel);
        $output->generateOutputCmd();
    }

    /**
     * Test if the Facebook output class generates the correct output command.
     */
    public function testValidGenerateOutputCmd()
    {
        $output = new Facebook($this->facebookChannel);
        $output->setStreamUrl('http://streamurl/video/');
        self::assertEquals('-vcodec copy -acodec copy -f flv "http://streamurl/video/"', $output->generateOutputCmd());
    }

    /**
     * Test the result of the output factory
     */
    public function testOutputFactory()
    {
        $outputFactory = OutputFactory::loadOutput($this->facebookChannel);
        self::assertEquals('Martin1982\LiveBroadcastBundle\Streams\Output\Facebook', get_class($outputFactory));
    }
}
