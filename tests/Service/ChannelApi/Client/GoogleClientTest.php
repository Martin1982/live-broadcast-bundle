<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi\Client;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config\GoogleConfig;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\GoogleClient;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class GoogleClientTest
 */
class GoogleClientTest extends TestCase
{
    /**
     * @var GoogleConfig|MockObject
     */
    protected $config;

    /**
     * @var GoogleRedirectService|MockObject
     */
    protected $redirect;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * Test getting the client
     *
     * @throws LiveBroadcastOutputException
     */
    public function testGetClient(): void
    {
        $this->config->expects(self::atLeastOnce())
            ->method('getClientId')
            ->willReturn('1');
        $this->config->expects(self::atLeastOnce())
            ->method('getClientSecret')
            ->willReturn('secret');

        $this->redirect->expects(self::atLeastOnce())
            ->method('getOAuthRedirectUrl')
            ->willReturn('https://some.url');

        $client = new GoogleClient($this->config, $this->redirect, $this->logger);
        $preparedClient = $client->getClient();

        self::assertEquals('1', $preparedClient->getClientId());
    }

    /**
     * Setup mock objects
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(GoogleConfig::class);
        $this->redirect = $this->createMock(GoogleRedirectService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }
}
