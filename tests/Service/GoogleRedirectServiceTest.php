<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class GoogleRedirectServiceTest
 */
class GoogleRedirectServiceTest extends TestCase
{
    /**
     * Test getting the redirect url
     *
     * @throws LiveBroadcastOutputException
     */
    public function testGetOAuthRedirectUrl(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())
            ->method('generate')
            ->willReturn('my_resulting_route');

        $redirectRoute = 'test';

        $redirect = new GoogleRedirectService($router, $redirectRoute);
        $url = $redirect->getOAuthRedirectUrl();

        self::assertEquals('my_resulting_route', $url);
    }

    /**
     * Test getting the redirect url
     */
    public function testRouteNotFound(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())
            ->method('generate')
            ->willThrowException(new RouteNotFoundException());

        $redirectRoute = 'test';

        $redirect = new GoogleRedirectService($router, $redirectRoute);
        $url = $redirect->getOAuthRedirectUrl();

        self::assertEquals('my_resulting_route', $url);
    }

    /**
     * Test getting the redirect url
     */
    public function testMissingMandatoryParameters(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())
            ->method('generate')
            ->willThrowException(new LiveBroadcastOutputException());

        $redirectRoute = 'test';

        $redirect = new GoogleRedirectService($router, $redirectRoute);
        $url = $redirect->getOAuthRedirectUrl();

        self::assertEquals('my_resulting_route', $url);
    }

    /**
     * Test getting the redirect url
     */
    public function testInvalidParameter(): void
    {
        $this->expectException(LiveBroadcastOutputException::class);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())
            ->method('generate')
            ->willThrowException(new LiveBroadcastOutputException());

        $redirectRoute = 'test';

        $redirect = new GoogleRedirectService($router, $redirectRoute);
        $url = $redirect->getOAuthRedirectUrl();

        self::assertEquals('my_resulting_route', $url);
    }
}
