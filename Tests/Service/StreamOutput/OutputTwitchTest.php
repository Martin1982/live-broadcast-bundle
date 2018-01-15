<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputTwitch;

/**
 * Class OutputTwitchTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput
 */
class OutputTwitchTest extends \PHPUnit_Framework_TestCase
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
     * Test if the class implements the OutputInterface
     */
    public function tesImplementsOutputInterface()
    {
        $implements = class_implements(OutputTwitch::class);
        self::assertTrue(in_array(OutputInterface::class, $implements, true));
    }

    /**
     * Test the generate output command without a channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithoutChannel()
    {
        $twitch = new OutputTwitch();
        $twitch->generateOutputCmd();
    }

    /**
     * Test the generate output command with an invalid channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithInvalidChannel()
    {
        $twitch = new OutputTwitch();
        $channel = new ChannelTwitch();
        $twitch->setChannel($channel);

        $twitch->generateOutputCmd();
    }

    /**
     * Test if the Twitch output class generates the correct output command.
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmd()
    {
        $twitch = new OutputTwitch();
        $twitch->setChannel($this->twitchChannel);
        self::assertEquals(
            $twitch->generateOutputCmd(),
            '-vcodec copy -acodec copy -f flv "rtmp://value1/app/value2"'
        );
    }

    /**
     * Test if the channelType is correct for this output
     */
    public function testGetChannelType()
    {
        $twitch = new OutputTwitch();
        self::assertEquals(ChannelTwitch::class, $twitch->getChannelType());
    }
}
