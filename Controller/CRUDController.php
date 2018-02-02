<?php

namespace Martin1982\LiveBroadcastBundle\Controller;

use Facebook\Authentication\AccessToken;
use Martin1982\LiveBroadcastBundle\Service\FacebookApiService;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class CRUDController
 *
 * @codeCoverageIgnore
 */
class CRUDController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function longLivedAccessTokenAction(Request $request)
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
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function youTubeOAuthAction(Request $request)
    {
        $youTubeService = $this->get('live.broadcast.youtubeapi.service');
        $router = $this->get('router');
        $redirectUri = $router->generate(
            'admin_martin1982_livebroadcast_channel_basechannel_youtubeoauth',
            [],
            $router::ABSOLUTE_URL
        );
        $youTubeService->initApiClients($redirectUri);
        $session = $request->getSession();

        if ($request->get('cleartoken')) {
            $this->clearToken($session, $youTubeService);
        }

        $requestCode = $request->get('code');
        if ($requestCode) {
            $this->checkRequestCode($request, $session, $youTubeService);
        }

        return $this->redirect($session->get('authreferer', '/'));
    }

    /**
     * @param Session $session
     * @param YouTubeApiService $youTubeService
     */
    protected function clearToken(Session $session, YouTubeApiService $youTubeService)
    {
        $session->remove('youTubeRefreshToken');
        $youTubeService->clearToken();
    }

    /**
     * @param Request $request
     * @param Session $session
     * @param YouTubeApiService $youTubeService
     */
    protected function checkRequestCode(Request $request, Session $session, YouTubeApiService $youTubeService)
    {
        $requestCode = $request->get('code');
        $requestState = $request->get('state');
        $sessionState = $session->get('state');

        $youTubeService->authenticate($requestCode, $requestState, $sessionState);
        $refreshToken = $youTubeService->getRefreshToken();

        if ($refreshToken) {
            $session->set('youTubeChannelName', $youTubeService->getChannelName());
            $session->set('youTubeRefreshToken', $refreshToken);
        }
    }
}
