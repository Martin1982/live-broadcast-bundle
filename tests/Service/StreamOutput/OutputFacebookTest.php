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
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastApiException;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class OutputFacebookTest
 */
class OutputFacebookTest extends TestCase
{
    /**
     * @var ChannelFacebook
     */
    private ChannelFacebook $facebookChannel;

    /**
     * @var FacebookApiService|MockObject
     */
    private $api;

    /**
     * Set up a testable Facebook channel
     * @throws Exception
     */
    public function setUp(): void
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
     * Test the output generation command without a channel
     * @throws LiveBroadcastApiException
     */
    public function testGenerateOutputCmdWithoutChannel(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $facebook = new OutputFacebook($this->api);
        $facebook->generateOutputCmd();
    }

    /**
     * Test the output generation command with an invalid channel
     * @throws LiveBroadcastApiException
     */
    public function testGenerateOutputCmdWithInvalidChannel(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $facebook = new OutputFacebook($this->api);
        $channel = new ChannelFacebook();
        $facebook->setChannel($channel);

        $facebook->generateOutputCmd();
    }

    /**
     * Test the output generation command without a stream url set
     * @throws LiveBroadcastApiException
     */
    public function testGenerateOutputCmdWithoutStreamUrl(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
        $facebook = new OutputFacebook($this->api);
        $facebook->setChannel($this->facebookChannel);
        $facebook->generateOutputCmd();
    }

    /**
     * Test if the Facebook output class generates the correct output command.
     *
     * @throws LiveBroadcastOutputException
     * @throws LiveBroadcastApiException
     * @throws Exception
     * @throws Exception
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
     * Test without a stream url
     *
     * @throws LiveBroadcastOutputException
     * @throws LiveBroadcastApiException
     * @throws Exception
     * @throws Exception
     */
    public function testNoStreamUrlException(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);
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
