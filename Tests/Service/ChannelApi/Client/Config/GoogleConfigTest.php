<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi\Client\Config;

use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config\GoogleConfig;
use PHPUnit\Framework\TestCase;

/**
 * Class GoogleConfigTest
 */
class GoogleConfigTest extends TestCase
{
    /**
     * @var GoogleConfig
     */
    protected $defaultConfig;

    /**
     * Test getting the client id
     */
    public function testGetClientId(): void
    {
        self::assertEquals('a', $this->defaultConfig->getClientId());
    }

    /**
     * Test getting the client secret
     */
    public function testGetClientSecret(): void
    {
        self::assertEquals('b', $this->defaultConfig->getClientSecret());
    }

    /**
     * Test object setup
     */
    protected function setUp(): void
    {
        $this->defaultConfig = new GoogleConfig('a', 'b');
    }
}
