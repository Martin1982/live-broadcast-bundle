<?php

namespace Martin1982\LiveBroadcastBundle\Service;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookSDK;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;

/**
 * Class FacebookApiService
 */
class FacebookApiService
{
    /**
     * @var FacebookSDK
     */
    private $facebookSDK;

    /**
     * FacebookApiService constructor.
     * @param string $applicationId
     * @param string $applicationSecret
     * @throws LiveBroadcastOutputException
     */
    public function __construct($applicationId, $applicationSecret)
    {
        if (empty($applicationId) || empty($applicationSecret)) {
            throw new LiveBroadcastOutputException('The Facebook application settings are not correct.');
        }

        try {
            $this->facebookSDK = new FacebookSDK([
                'app_id' => $applicationId,
                'app_secret' => $applicationSecret,
            ]);
        } catch (FacebookSDKException $ex) {
            throw new LiveBroadcastOutputException('Facebook SDK Exception: '.$ex->getMessage());
        }
    }

    /**
     * @param LiveBroadcast  $liveBroadcast
     * @param OutputFacebook $outputFacebook
     * @return null|string
     * @throws LiveBroadcastOutputException
     */
    public function createFacebookLiveVideo(LiveBroadcast $liveBroadcast, OutputFacebook $outputFacebook)
    {
        try {
            $params = array('title' => $liveBroadcast->getName(),
                            'description' => $liveBroadcast->getDescription());

            $this->facebookSDK->setDefaultAccessToken($outputFacebook->getAccessToken());
            $response = $this->facebookSDK->post($outputFacebook->getEntityId().'/live_videos', $params);
        } catch (FacebookResponseException $ex) {
            throw new LiveBroadcastOutputException('Facebook exception: '.$ex->getMessage());
        } catch (FacebookSDKException $ex) {
            throw new LiveBroadcastOutputException('Facebook SDK exception: '.$ex->getMessage());
        }

        $body = $response->getDecodedBody();

        if (array_key_exists('stream_url', $body)) {
            return $body['stream_url'];
        }

        return null;
    }

    /**
     * @param string $userAccessToken
     * @return \Facebook\Authentication\AccessToken|null
     * @throws LiveBroadcastOutputException
     */
    public function getLongLivedAccessToken($userAccessToken)
    {
        if (!$userAccessToken) {
            return null;
        }

        try {
            return $this->facebookSDK->getOAuth2Client()->getLongLivedAccessToken($userAccessToken);
        } catch (FacebookSDKException $ex) {
            throw new LiveBroadcastOutputException('Facebook SDK exception: '.$ex->getMessage());
        }
    }
}
