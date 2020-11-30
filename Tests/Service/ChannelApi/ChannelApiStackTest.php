<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiStack;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use PHPUnit\Framework\TestCase;

/**
 * Class ChannelApiStackTest
 */
class ChannelApiStackTest extends TestCase
{
    /**
     * Test that an API for a channel can be retrieved
     */
    public function testGetApiForChannel(): void
    {
        $youTubeChannel = new ChannelYouTube();
        $facebookChannel = new ChannelFacebook();

        $apiStack = new ChannelApiStack();
        $apiStack->addApi($this->createMock(YouTubeApiService::class));
        $apiStack->addApi($this->createMock(FacebookApiService::class));

        self::assertInstanceOf(YouTubeApiService::class, $apiStack->getApiForChannel($youTubeChannel));
        self::assertInstanceOf(FacebookApiService::class, $apiStack->getApiForChannel($facebookChannel));
    }

    /**
     * Test that no API is returned when it's not available
     */
    public function testCannotFindApiForChannel(): void
    {
        $twitchChannel = new ChannelTwitch();
        $apiStack = new ChannelApiStack();

        self::assertNull($apiStack->getApiForChannel($twitchChannel));
    }
}
