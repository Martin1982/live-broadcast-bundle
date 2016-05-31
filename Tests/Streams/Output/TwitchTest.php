<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Streams\Output;

use Martin1982\LiveBroadcastBundle\Streams\Output\Twitch;

/**
 * Class TwitchTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Streams\Output
 */
class TwitchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if the Twitch output class implements the correct interface
     */
    public function testTwitchContstructor()
    {
        $twitchOutput = new Twitch('dummy', 'dummy');
        $this->assertInstanceOf('Martin1982\LiveBroadcastBundle\Streams\Output\Twitch', $twitchOutput);
    }

    /**
     * Test if the Twitch output class generates the correct output command
     */
    public function testGenerateOutputCmd()
    {
        $twitchOutput = new Twitch('value1', 'value2');
        $this->assertEquals($twitchOutput->generateOutputCmd(), '-f flv rtmp://value1/app/value2');
    }
}