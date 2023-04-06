<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use PHPUnit\Framework\TestCase;

/**
 * Class OutputYouTubeTest
 */
class OutputYouTubeTest extends TestCase
{
    /**
     * @var OutputYouTube
     */
    private OutputYouTube $youTube;

    /**
     * Set up a testable Facebook channel
     */
    public function setUp(): void
    {
        $api = $this->createMock(YouTubeApiService::class);
        $this->youTube = new OutputYouTube($api);
        $this->youTube->setChannel(new ChannelYouTube());
    }

    /**
     *
     */
    public function testChannel(): void
    {
        self::assertNotNull($this->youTube->getChannel());
    }

    /**
     *
     */
    public function testChannelType(): void
    {
        self::assertEquals(ChannelYouTube::class, $this->youTube->getChannelType());
    }

    /**
     * Test generating an output throws an exception
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGenerateOutputCmdException(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $this->youTube->generateOutputCmd();
    }

    /**
     * Test that no stream url throws an exception
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testNoStreamUrlException(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $api = $this->createMock(YouTubeApiService::class);
        $broadcast = $this->createMock(LiveBroadcast::class);

        $youTube = new OutputYouTube($api);
        $youTube->setBroadcast($broadcast);
        $youTube->getStreamUrl();
    }

    /**
     * @throws LiveBroadcastOutputException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGenerateOutputCmd(): void
    {
        $api = $this->createMock(YouTubeApiService::class);
        $api->expects(static::atLeastOnce())
            ->method('getStreamUrl')
            ->willReturn('stream.url');

        $broadcast = $this->createMock(LiveBroadcast::class);

        $this->youTube = new OutputYouTube($api);
        $this->youTube->setChannel(new ChannelYouTube());
        $this->youTube->setBroadcast($broadcast);

        self::assertEquals(
            // @codingStandardsIgnoreLine
            '-vf scale=-1:720 -c:v libx264 -pix_fmt yuv420p -preset veryfast -r 30 -g 60 -b:v 4000k -c:a aac -f flv "stream.url"',
            $this->youTube->generateOutputCmd()
        );
    }
}
