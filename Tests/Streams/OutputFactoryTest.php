<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Streams;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Streams\OutputFactory;

/**
 * Class OutputFactoryTest.
 */
class OutputFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test an unknown output for a channel.
     *
     * @expectedException \Exception
     */
    public function testUnknownOutputFactory()
    {
        OutputFactory::loadOutput('unknown');
    }

    /**
     * Test output factory with Twitch.
     */
    public function testTwitchOutputFactory()
    {
        $twitchChannel = new ChannelTwitch();
        $outputFactory = OutputFactory::loadOutput($twitchChannel);

        self::assertInstanceOf('Martin1982\LiveBroadcastBundle\Streams\Output\Twitch', $outputFactory);
    }
}
