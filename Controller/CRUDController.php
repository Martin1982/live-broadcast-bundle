<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Controller;

use Facebook\Authentication\AccessToken;
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
        $facebookService = $this->get('live.broadcast.facebook_api.service');
        $accessToken = $facebookService->getLongLivedAccessToken($request->get('userAccessToken'));

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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function youTubeOAuthAction(Request $request): RedirectResponse
    {
        $session = $request->getSession();

        if ($session && $request->get('cleartoken')) {
            $this->clearToken($session);
        }

        $requestCode = $request->get('code');
        if ($requestCode && $session) {
            $this->checkRequestCode($request, $session);
        }

        return $this->redirect($session->get('authreferer', '/'));
    }

    /**
     * @param SessionInterface $session
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    protected function clearToken(SessionInterface $session): void
    {
        $session->remove('youTubeRefreshToken');

        $googleClient = $this->getGoogleClient();
        $googleClient->revokeToken();
    }

    /**
     * @param Request          $request
     * @param SessionInterface $session
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    protected function checkRequestCode(Request $request, SessionInterface $session): void
    {
        $requestCode = $request->get('code');
        $requestState = $request->get('state', 'norequeststate');
        $sessionState = $session->get('state', 'nosessionstate');

        $googleClient = $this->getGoogleClient();

        if ($sessionState !== $requestState) {
            $googleClient->fetchAccessTokenWithAuthCode($requestCode);
            $googleClient->getAccessToken();
        }
        $refreshToken = $googleClient->getRefreshToken();

        if ($refreshToken) {
            $youtubeClient = new \Google_Service_YouTube($googleClient);
            $channels = $youtubeClient->channels->listChannels('id,brandingSettings', [ 'mine' => true ]);

            $hasChannels = $channels->count() > 0;

            if ($hasChannels) {
                /** @var \Google_Service_YouTube_Channel $channel */
                $channel = $channels->current();

                /** @var \Google_Service_YouTube_ChannelBrandingSettings $branding */
                $branding = $channel->getBrandingSettings();
                $title = $branding->getChannel()->title;

                $session->set('youTubeChannelName', $title);
                $session->set('youTubeRefreshToken', $refreshToken);
            }
        }
    }

    /**
     * @return \Google_Client
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    private function getGoogleClient(): \Google_Client
    {
        $clientService = $this->container->get('live.broadcast.channel_api.client.google');

        return $clientService->getClient();
    }
}
