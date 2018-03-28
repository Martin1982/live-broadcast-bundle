<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class GoogleRedirectService
 */
class GoogleRedirectService
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $redirectRoute;

    /**
     * GoogleRedirectService constructor
     *
     * @param RouterInterface $router
     * @param string|null     $redirectRoute
     */
    public function __construct(RouterInterface $router, $redirectRoute = null)
    {
        $this->router = $router;
        $this->redirectRoute = $redirectRoute;
    }

    /**
     * @return string
     *
     * @throws LiveBroadcastOutputException
     */
    public function getOAuthRedirectUrl(): string
    {
        $router = $this->router;

        try {
            return $this->router->generate(
                $this->redirectRoute,
                [],
                $router::ABSOLUTE_URL
            );
        } catch (RouteNotFoundException $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        } catch (MissingMandatoryParametersException $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        } catch (InvalidParameterException $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        }
    }
}
