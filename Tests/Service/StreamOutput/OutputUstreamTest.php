<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelUstream;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputUstream;

/**
 * Class OutputUstreamTest.
 */
class OutputUstreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChannelUstream
     */
    private $channelUstream;

    /**
     * Setup a testable Ustream channel.
     */
    public function setUp()
    {
        $this->channelUstream = new ChannelUstream();
        $this->channelUstream->setStreamServer('server');
        $this->channelUstream->setStreamKey('key');
    }

    /**
     * Test if the class implements the OutputInterface
     */
    public function tesImplementsOutputInterface()
    {
        $implements = class_implements(OutputUstream::class);
        self::assertTrue(in_array(OutputInterface::class, $implements));
    }

    /**
     * Test the generate output command without a channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithoutChannel()
    {
        $ustream = new OutputUstream();
        $ustream->generateOutputCmd();
    }

    /**
     * Test the generate output command with an invalid channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithInvalidChannel()
    {
        $ustream = new OutputUstream();
        $channel = new ChannelUstream();
        $ustream->setChannel($channel);

        $ustream->generateOutputCmd();
    }

    /**
     * Test if the Ustream output class generates the correct output command.
     */
    public function testGenerateOutputCmd()
    {
        $ustream = new OutputUstream();
        $ustream->setChannel($this->channelUstream);
        self::assertEquals(
            $ustream->generateOutputCmd(),
            '-vcodec copy -acodec copy -f flv "rtmp://server/key"'
        );
    }

    /**
     * Test if the channelType is correct for this output
     */
    public function testGetChannelType()
    {
        $ustream = new OutputUstream();
        self::assertEquals(ChannelUstream::class, $ustream->getChannelType());
    }
}
