<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputTwitch;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;

/**
 * Class StreamOutputServiceTest
 */
class StreamOutputServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testGetOutputInterfaceWithoutChannels()
    {
        $channel = new ChannelFacebook();
        $service = new StreamOutputService();
        $service->getOutputInterface($channel);
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
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
