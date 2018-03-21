<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
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
    private $youTube;

    /**
     * Setup a testable Facebook channel
     */
    public function setUp()
    {
        $api = $this->createMock(YouTubeApiService::class);
        $api->expects(static::any())
            ->method('getStreamUrl')
            ->willReturn('stream.url');

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
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdException(): void
    {
        $this->youTube->generateOutputCmd();
    }

    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmd(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);

        $this->youTube->setBroadcast($broadcast);
        self::assertEquals(
            // @codingStandardsIgnoreLine
            '-vf scale=-1:720 -c:v libx264 -pix_fmt yuv420p -preset veryfast -r 30 -g 60 -b:v 4000k -c:a aac -f flv "stream.url"',
            $this->youTube->generateOutputCmd()
        );
    }
}
