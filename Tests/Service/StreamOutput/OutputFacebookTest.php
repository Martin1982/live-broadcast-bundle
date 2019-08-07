<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class OutputFacebookTest
 */
class OutputFacebookTest extends TestCase
{
    /**
     * @var ChannelFacebook
     */
    private $facebookChannel;

    /**
     * @var FacebookApiService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $api;

    /**
     * Setup a testable Facebook channel
     */
    public function setUp()
    {
        $this->facebookChannel = new ChannelFacebook();
        $this->facebookChannel->setAccessToken('token');
        $this->facebookChannel->setFbEntityId('id');

        $this->api = $this->createMock(FacebookApiService::class);
    }

    /**
     * Test if the class implements the OutputInterface
     */
    public function tesImplementsOutputInterface(): void
    {
        $implements = class_implements(OutputFacebook::class);
        self::assertContains(OutputInterface::class, $implements);
    }

    /**
     * Test the generate output command without a channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithoutChannel(): void
    {
        $facebook = new OutputFacebook($this->api);
        $facebook->generateOutputCmd();
    }

    /**
     * Test the generate output command with an invalid channel
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithInvalidChannel(): void
    {
        $facebook = new OutputFacebook($this->api);
        $channel = new ChannelFacebook();
        $facebook->setChannel($channel);

        $facebook->generateOutputCmd();
    }

    /**
     * Test the generate output command without a stream url set
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGenerateOutputCmdWithoutStreamUrl(): void
    {
        $facebook = new OutputFacebook($this->api);
        $facebook->setChannel($this->facebookChannel);
        $facebook->generateOutputCmd();
    }

    /**
     * Test if the Facebook output class generates the correct output command.
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testValidGenerateOutputCmd(): void
    {
        $this->api->expects(static::atLeastOnce())
            ->method('getStreamUrl')
            ->willReturn('http://streamurl/video/');

        $facebook = new OutputFacebook($this->api);
        $facebook->setBroadcast($this->createMock(LiveBroadcast::class));
        $facebook->setChannel($this->createMock(AbstractChannel::class));
        $facebook->setChannel($this->facebookChannel);
        self::assertEquals(
            '-c:v libx264 -crf 30 -preset ultrafast -c:a copy -f flv "http://streamurl/video/"',
            $facebook->generateOutputCmd()
        );
    }

    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testNoStreamUrlException(): void
    {
        $api = $this->createMock(FacebookApiService::class);
        $broadcast = $this->createMock(LiveBroadcast::class);

        $facebook = new OutputFacebook($api);
        $facebook->setBroadcast($broadcast);
        $facebook->getStreamUrl();
    }

    /**
     * Test if the channelType is correct for this output
     */
    public function testGetChannelType(): void
    {
        $facebook = new OutputFacebook($this->api);
        self::assertEquals(ChannelFacebook::class, $facebook->getChannelType());
    }
}
