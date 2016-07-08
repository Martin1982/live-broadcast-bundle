<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Service;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookSDK;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Streams\Output\Facebook as FacebookOutput;

/**
 * Class FacebookLiveService
 */
class FacebookLiveService
{
    /**
     * @var FacebookSDK
     */
    private $facebookSDK;

    /**
     * FacebookLiveService constructor.
     * @param string $applicationId
     * @param string $applicationSecret
     * @throws LiveBroadcastException
     */
    public function __construct($applicationId, $applicationSecret)
    {
        if (empty($applicationId) || empty($applicationSecret)) {
            throw new LiveBroadcastException('The Facebook application settings are not correct.');
        }

        $this->facebookSDK = new FacebookSDK([
            'app_id' => $applicationId,
            'app_secret' => $applicationSecret,
        ]);
    }

    /**
     * @param LiveBroadcast  $liveBroadcast
     * @param FacebookOutput $facebookOutput
     * @return null|string
     * @throws LiveBroadcastException
     */
    public function createFacebookLiveVideo(LiveBroadcast $liveBroadcast, FacebookOutput $facebookOutput)
    {
        try {
            $params = array('title' => $liveBroadcast->getName(),
                            'description' => $liveBroadcast->getDescription());

            $this->facebookSDK->setDefaultAccessToken($facebookOutput->getAccessToken());
            $response = $this->facebookSDK->post($facebookOutput->getEntityId().'/live_videos', $params);
        } catch (FacebookResponseException $ex) {
            throw new LiveBroadcastException('Facebook exception: '.$ex->getMessage());
        } catch (FacebookSDKException $ex) {
            throw new LiveBroadcastException('Facebook SDK exception: '.$ex->getMessage());
        }

        $body = $response->getDecodedBody();

        if (array_key_exists('stream_url', $body)) {
            return $body['stream_url'];
        }
    }

    /**
     * @param string $userAccessToken
     * @return \Facebook\Authentication\AccessToken|null
     * @throws LiveBroadcastException
     */
    public function getLongLivedAccessToken($userAccessToken)
    {
        if (!$userAccessToken) {
            return null;
        }

        try {
            return $this->facebookSDK->getOAuth2Client()->getLongLivedAccessToken($userAccessToken);
        } catch (FacebookSDKException $ex) {
            throw new LiveBroadcastException('Facebook SDK exception: '.$ex->getMessage());
        }

        return null;
    }
}
