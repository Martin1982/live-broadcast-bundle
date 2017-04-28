<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelLively;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputLively;

/**
 * Class OutputLivelyTestTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput
 */
class OutputLivelyTestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChannelLively
     */
    private $channelLively;

    /**
     * Setup a testable Lively channel.
     */
    public function setUp()
    {
        $this->channelLively = new ChannelLively();
        $this->channelLively->setStreamServer('server');
        $this->channelLively->setStreamKey('secret');
    }

    /**
     * Test if the class implements the OutputInterface
     */
    public function tesImplementsOutputInterface()
    {
        $implements = class_implements(OutputLively::class);
        self::assertTrue(in_array(OutputInterface::class, $implements));
    }

    /**
     * Test the generate output command without a channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithoutChannel()
    {
        $lively = new OutputLively();
        $lively->generateOutputCmd();
    }

    /**
     * Test the generate output command with an invalid channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithInvalidChannel()
    {
        $lively = new OutputLively();
        $channel = new ChannelLively();
        $lively->setChannel($channel);

        $lively->generateOutputCmd();
    }

    /**
     * Test if the Live.ly output class generates the correct output command.
     */
    public function testGenerateOutputCmd()
    {
        $lively = new OutputLively();
        $lively->setChannel($this->channelLively);
        self::assertEquals(
            $lively->generateOutputCmd(),
            '-vcodec copy -acodec copy -f flv "rtmp://server/secret"'
        );
    }

    /**
     * Test if the channelType is correct for this output
     */
    public function testGetChannelType()
    {
        $lively = new OutputLively();
        self::assertEquals(ChannelLively::class, $lively->getChannelType());
    }
}
