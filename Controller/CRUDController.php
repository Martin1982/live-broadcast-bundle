<?php

namespace Martin1982\LiveBroadcastBundle\Controller;

use Facebook\Authentication\AccessToken;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\FacebookLiveService;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CRUDController
  */
class CRUDController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function longLivedAccessTokenAction(Request $request)
    {
        /** @var FacebookLiveService $facebookService */
        $facebookService = $this->get('live.broadcast.facebooklive.service');
        $accessToken = $facebookService->getLongLivedAccessToken($request->get('userAccessToken', null));

        if ($accessToken instanceof AccessToken) {
            return new JsonResponse(array('accessToken' => $accessToken->getValue()));
        }

        return new JsonResponse(null, 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function youTubeAccessTokenAction(Request $request)
    {
        $youTubeService = $this->get('live.broadcast.youtubelive.service');
        $session = $request->getSession();

        $requestCode = $request->get('code');

        $requestState = $request->get('state');
        $sessionState = $session->get('state');

        if (isset($requestState)) {
            if (strval($sessionState) !== strval($requestState)) {
                throw new LiveBroadcastException('The session state did not match');
            }

            $youTubeService->authenticate($requestCode);
            $session->set('token', $youTubeService->getAccessToken());
            $youTubeService->setRedirectUrl($request->get('redirectUrl'));
        }

        if (isset($sessionToken)) {
            $youTubeService->setAccessToken($session->get('token'));
        }

        if ($youTubeService->getAccessToken()) {

        } else {
            $state = mt_rand();
            $youTubeService->setState($state);
            $session->set('state', $state);
            $authUrl = $youTubeService->createAuthUrl();

            return new JsonResponse(array(
                'authUrl' => $authUrl
            ), 401);
        }

        return new JsonResponse(null, 500);
    }
}
