<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
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
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGetOutputInterfaceWithoutChannels()
    {
        $channel = new ChannelFacebook();
        $service = new StreamOutputService();
        $service->getOutputInterface($channel);
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGetOutputInterfaceWithNotConfiguredChannel()
    {
        $outputFacebook = new OutputFacebook();
        $channelTwitch = new ChannelTwitch();

        $service = new StreamOutputService();
        $service->addStreamOutput($outputFacebook, 'Facebook');
        $service->getOutputInterface($channelTwitch);
    }

    /**
     * Test if the correct output instance is returned for a given channel
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGetOutputInterface()
    {
        $outputFacebook = new OutputFacebook();
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
