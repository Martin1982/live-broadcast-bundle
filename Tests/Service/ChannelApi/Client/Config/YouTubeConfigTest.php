<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi\Client\Config;

use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config\YouTubeConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class YouTubeConfigTest
 */
class YouTubeConfigTest extends TestCase
{
    /**
     * @var YouTubeConfig
     */
    protected $config;

    /**
     * Test getting the host
     */
    public function testGetHost(): void
    {
        self::assertEquals('a.host.com', $this->config->getHost());
    }

    /**
     * Test getting the thumbnail directory
     */
    public function testGetThumbnailDirectory(): void
    {
        self::assertEquals('/a/directory', $this->config->getThumbnailDirectory());
    }

    /**
     * Setup basic object
     */
    protected function setUp()
    {
        $this->config = new YouTubeConfig('a.host.com', '/a/directory');
    }
}
