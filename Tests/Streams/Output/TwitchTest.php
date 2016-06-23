<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Streams\Output;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Streams\Output\Twitch;
use Martin1982\LiveBroadcastBundle\Streams\OutputFactory;

/**
 * Class TwitchTest.
 */
class TwitchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChannelTwitch
     */
    private $twitchChannel;

    /**
     * Setup a testable Twitch channel.
     */
    public function setUp()
    {
        $this->twitchChannel = new ChannelTwitch();
        $this->twitchChannel->setStreamServer('value1');
        $this->twitchChannel->setStreamKey('value2');
    }

    /**
     * Test if the Twitch output class implements the correct interface.
     */
    public function testTwitchConstructor()
    {
        $twitchOutput = new Twitch($this->twitchChannel);
        self::assertInstanceOf('Martin1982\LiveBroadcastBundle\Streams\Output\Twitch', $twitchOutput);
    }

    /**
     * Test if the Twitch output class generates the correct output command.
     */
    public function testGenerateOutputCmd()
    {
        $twitchOutput = new Twitch($this->twitchChannel);
        self::assertEquals($twitchOutput->generateOutputCmd(), '-vcodec copy -acodec copy -f flv "rtmp://value1/app/value2"');
    }

    /**
     * Test the result of the output factory.
     */
    public function testOutputFactory()
    {
        $outputFactory = OutputFactory::loadOutput($this->twitchChannel);
        self::assertEquals('Martin1982\LiveBroadcastBundle\Streams\Output\Twitch', get_class($outputFactory));
    }
}
