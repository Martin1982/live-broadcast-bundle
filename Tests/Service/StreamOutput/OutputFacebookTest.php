<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
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
        self::assertTrue(\in_array(OutputInterface::class, $implements, true));
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
        $facebook = new OutputFacebook($this->api);
        $facebook->setChannel($this->facebookChannel);
        $facebook->setStreamUrl('http://streamurl/video/');
        self::assertEquals(
            '-c:v libx264 -crf 30 -preset ultrafast -c:a copy -f flv "http://streamurl/video/"',
            $facebook->generateOutputCmd()
        );
    }

    /**
     * Test if the channelType is correct for this output
     */
    public function testGetChannelType(): void
    {
        $facebook = new OutputFacebook($this->api);
        self::assertEquals(ChannelFacebook::class, $facebook->getChannelType());
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testAccessTokenException(): void
    {
        $facebook = new OutputFacebook($this->api);
        $facebook->getAccessToken();
    }

    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testAccessToken(): void
    {
        $facebook = new OutputFacebook($this->api);
        $facebook->setChannel($this->facebookChannel);
        self::assertEquals('token', $facebook->getAccessToken());
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testEntityIdException(): void
    {
        $facebook = new OutputFacebook($this->api);
        $facebook->getEntityId();
    }

    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testEntityId(): void
    {
        $facebook = new OutputFacebook($this->api);
        $facebook->setChannel($this->facebookChannel);
        self::assertEquals('id', $facebook->getEntityId());
    }
}
