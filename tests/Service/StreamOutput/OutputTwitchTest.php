<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputTwitch;
use PHPUnit\Framework\TestCase;

/**
 * Class OutputTwitchTest
 */
class OutputTwitchTest extends TestCase
{
    /**
     * @var ChannelTwitch
     */
    private ChannelTwitch $twitchChannel;

    /**
     * Set up a testable Twitch channel.
     */
    public function setUp(): void
    {
        $this->twitchChannel = new ChannelTwitch();
        $this->twitchChannel->setStreamServer('value1');
        $this->twitchChannel->setStreamKey('value2');
    }

    /**
     * Test if the class implements the OutputInterface
     */
    public function tesImplementsOutputInterface(): void
    {
        $implements = class_implements(OutputTwitch::class);
        self::assertContains(OutputInterface::class, $implements);
    }

    /**
     * Test the output generation command without a channel
     */
    public function testGenerateOutputCmdWithoutChannel(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $twitch = new OutputTwitch();
        $twitch->generateOutputCmd();
    }

    /**
     * Test the output generation command with an invalid channel
     */
    public function testGenerateOutputCmdWithInvalidChannel(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $twitch = new OutputTwitch();
        $channel = new ChannelTwitch();
        $twitch->setChannel($channel);

        $twitch->generateOutputCmd();
    }

    /**
     * Test if the Twitch output class generates the correct output command.
     *
     * @throws LiveBroadcastOutputException
     */
    public function testGenerateOutputCmd(): void
    {
        $twitch = new OutputTwitch();
        $twitch->setChannel($this->twitchChannel);
        self::assertEquals(
            '-vcodec copy -acodec copy -f flv "rtmp://value1/app/value2"',
            $twitch->generateOutputCmd()
        );
    }

    /**
     * Test if the channelType is correct for this output
     */
    public function testGetChannelType(): void
    {
        $twitch = new OutputTwitch();
        self::assertEquals(ChannelTwitch::class, $twitch->getChannelType());
    }
}
