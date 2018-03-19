<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Controller;

use Facebook\Authentication\AccessToken;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class CRUDController
 *
 * @codeCoverageIgnore
 */
class CRUDController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function longLivedAccessTokenAction(Request $request): JsonResponse
    {
        /** @var FacebookApiService $facebookService */
        $facebookService = $this->get('live.broadcast.facebookapi.service');
        $accessToken = $facebookService->getLongLivedAccessToken($request->get('userAccessToken', null));

        if ($accessToken instanceof AccessToken) {
            return new JsonResponse(['accessToken' => $accessToken->getValue()]);
        }

        return new JsonResponse(null, 500);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function youTubeOAuthAction(Request $request): RedirectResponse
    {
        $youTubeService = $this->get('live.broadcast.youtubeapi.service');
        $router = $this->get('router');
        /** @noinspection PhpRouteMissingInspection */
        $redirectUri = $router->generate(
            'admin_martin1982_livebroadcast_channel_abstractchannel_youtubeoauth',
            [],
            $router::ABSOLUTE_URL
        );
        $youTubeService->initApiClients($redirectUri);
        $session = $request->getSession();

        if ($session && $request->get('cleartoken')) {
            $this->clearToken($session, $youTubeService);
        }

        $requestCode = $request->get('code');
        if ($requestCode && $session) {
            $this->checkRequestCode($request, $session, $youTubeService);
        }

        return $this->redirect($session->get('authreferer', '/'));
    }

    /**
     * @param SessionInterface  $session
     * @param YouTubeApiService $youTubeService
     *
     * @todo no cleartoken method in service
     */
    protected function clearToken(SessionInterface $session, YouTubeApiService $youTubeService): void
    {
        $session->remove('youTubeRefreshToken');
        $youTubeService->clearToken();
    }

    /**
     * @param Request           $request
     * @param SessionInterface  $session
     * @param YouTubeApiService $youtube
     *
     * @todo no authenticate method in service
     */
    protected function checkRequestCode(Request $request, SessionInterface $session, YouTubeApiService $youtube): void
    {
        $requestCode = $request->get('code');
        $requestState = $request->get('state');
        $sessionState = $session->get('state');

        $youtube->authenticate($requestCode, $requestState, $sessionState);
        $refreshToken = $youtube->getRefreshToken();

        if ($refreshToken) {
            $session->set('youTubeChannelName', $youtube->getChannelName());
            $session->set('youTubeRefreshToken', $refreshToken);
        }
    }
}
