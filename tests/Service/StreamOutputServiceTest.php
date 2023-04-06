<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputTwitch;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamOutputServiceTest
 */
class StreamOutputServiceTest extends TestCase
{
    /**
     * Test that not having a channel throws an exception
     */
    public function testGetOutputInterfaceWithoutChannels(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);

        $channel = new ChannelFacebook();
        $service = new StreamOutputService();
        $service->getOutputInterface($channel);
    }

    /**
     * Test that not having a configured channel throws an exception
     */
    public function testGetOutputInterfaceWithNotConfiguredChannel(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);

        $apiService = $this->createMock(FacebookApiService::class);

        $outputFacebook = new OutputFacebook($apiService);
        $channelTwitch = new ChannelTwitch();

        $service = new StreamOutputService();
        $service->addStreamOutput($outputFacebook, 'Facebook');
        $service->getOutputInterface($channelTwitch);
    }

    /**
     * Test if the correct output instance is returned for a given channel
     *
     * @throws LiveBroadcastOutputException
     */
    public function testGetOutputInterface(): void
    {
        $apiService = $this->createMock(FacebookApiService::class);

        $outputFacebook = new OutputFacebook($apiService);
        $outputTwitch = new OutputTwitch();

        $channelTwitch = new ChannelTwitch();
        $channelTwitch->setStreamKey('key');
        $channelTwitch->setStreamServer('server');

        $service = new StreamOutputService();
        $service->addStreamOutput($outputFacebook, 'Facebook');
        $service->addStreamOutput($outputTwitch, 'Twitch');

        $twitch = $service->getOutputInterface($channelTwitch);

        self::assertEquals('-vcodec copy -acodec copy -f flv "rtmp://server/app/key"', $twitch->generateOutputCmd());
    }
}
