<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class GoogleRedirectServiceTest
 */
class GoogleRedirectServiceTest extends TestCase
{
    /**
     * Test getting the redirect url
     */
    public function testGetOAuthRedirectUrl()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willReturn('myresultingroute');

        $redirectRoute = 'test';

        $redirect = new GoogleRedirectService($router, $redirectRoute);
        $url = $redirect->getOAuthRedirectUrl();

        self::assertEquals('myresultingroute', $url);
    }

    /**
     * Test getting the redirect url
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testRouteNotFound()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willThrowException(new RouteNotFoundException());

        $redirectRoute = 'test';

        $redirect = new GoogleRedirectService($router, $redirectRoute);
        $url = $redirect->getOAuthRedirectUrl();

        self::assertEquals('myresultingroute', $url);
    }

    /**
     * Test getting the redirect url
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testMissingMandatoryParameters()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willThrowException(new MissingMandatoryParametersException());

        $redirectRoute = 'test';

        $redirect = new GoogleRedirectService($router, $redirectRoute);
        $url = $redirect->getOAuthRedirectUrl();

        self::assertEquals('myresultingroute', $url);
    }

    /**
     * Test getting the redirect url
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testInvalidParameter()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())
            ->method('generate')
            ->willThrowException(new InvalidParameterException());

        $redirectRoute = 'test';

        $redirect = new GoogleRedirectService($router, $redirectRoute);
        $url = $redirect->getOAuthRedirectUrl();

        self::assertEquals('myresultingroute', $url);
    }
}
