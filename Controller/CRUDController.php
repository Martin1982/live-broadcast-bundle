<?php

namespace Martin1982\LiveBroadcastBundle\Controller;

use Facebook\Authentication\AccessToken;
use Martin1982\LiveBroadcastBundle\Service\FacebookLiveService;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @return RedirectResponse
     */
    public function youtubeOAuthAction(Request $request)
    {
        $youTubeService = $this->get('live.broadcast.youtubelive.service');
        $redirectUrl = $youTubeService->getReferUrl();

        return $this->redirect($redirectUrl);
    }
}
