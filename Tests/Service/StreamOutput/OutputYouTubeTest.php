<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use PHPUnit\Framework\TestCase;

/**
 * Class OutputYouTubeTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput
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
        $this->youTube = new OutputYouTube();
        $this->youTube->setChannel(new ChannelYouTube());
    }

    /**
     *
     */
    public function testChannel()
    {
        self::assertInstanceOf(ChannelYouTube::class, $this->youTube->getChannel());
    }

    /**
     *
     */
    public function testChannelType()
    {
        self::assertEquals(ChannelYouTube::class, $this->youTube->getChannelType());
    }

    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdException()
    {
        $this->youTube->generateOutputCmd();
    }

    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmd()
    {
        $this->youTube->setStreamUrl('stream.url');
        self::assertEquals(
            // @codingStandardsIgnoreLine
            '-vf scale=-1:720 -c:v libx264 -pix_fmt yuv420p -preset veryfast -r 30 -g 60 -b:v 4000k -c:a aac -f flv "stream.url"',
            $this->youTube->generateOutputCmd()
        );
    }
}
