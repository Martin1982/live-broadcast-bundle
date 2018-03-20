<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelLively;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputLively;
use PHPUnit\Framework\TestCase;

/**
 * Class OutputLivelyTestTest
 */
class OutputLivelyTest extends TestCase
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
        $this->channelLively->setStreamServer('rtmp://live.ly.server');
        $this->channelLively->setStreamKey('secret');
    }

    /**
     * Test if the class implements the OutputInterface
     */
    public function tesImplementsOutputInterface(): void
    {
        $implements = class_implements(OutputLively::class);
        self::assertTrue(\in_array(OutputInterface::class, $implements, true));
    }

    /**
     * Test the generate output command without a channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithoutChannel(): void
    {
        $lively = new OutputLively();
        $lively->generateOutputCmd();
    }

    /**
     * Test the generate output command with an invalid channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithInvalidChannel(): void
    {
        $lively = new OutputLively();
        $channel = new ChannelLively();
        $lively->setChannel($channel);

        $lively->generateOutputCmd();
    }

    /**
     * Test if the Live.ly output class generates the correct output command.
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmd(): void
    {
        $lively = new OutputLively();
        $lively->setChannel($this->channelLively);
        self::assertEquals(
            $lively->generateOutputCmd(),
            '-vcodec copy -acodec copy -f flv "rtmp://live.ly.server/secret"'
        );
    }

    /**
     * Test if the channelType is correct for this output
     */
    public function testGetChannelType(): void
    {
        $lively = new OutputLively();
        self::assertEquals(ChannelLively::class, $lively->getChannelType());
    }
}
