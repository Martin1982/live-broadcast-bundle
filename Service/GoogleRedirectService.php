<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
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
    protected RouterInterface $router;

    /**
     * @var string
     */
    protected string $redirectRoute = 'martin1982_livebroadcast_admin_youtubeoauth';

    /**
     * GoogleRedirectService constructor
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return string
     */
    public function getRedirectRoute(): string
    {
        return $this->redirectRoute;
    }

    /**
     * @param string $redirectRoute
     *
     * @return GoogleRedirectService
     */
    public function setRedirectRoute(string $redirectRoute): GoogleRedirectService
    {
        $this->redirectRoute = $redirectRoute;

        return $this;
    }

    /**
     * @return string
     *
     * @throws LiveBroadcastOutputException
     */
    public function getOAuthRedirectUrl(): string
    {
        try {
            return $this->router->generate(
                $this->getRedirectRoute(),
                [],
                $this->router::ABSOLUTE_URL
            );
        } catch (RouteNotFoundException $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        }
    }
}
