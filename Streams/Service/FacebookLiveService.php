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
 * @package Martin1982\LiveBroadcastBundle\Streams\Service
 */
class FacebookLiveService
{
    /**
     * @param LiveBroadcast  $liveBroadcast
     * @param FacebookOutput $facebookOutput
     * @return null|string
     * @throws LiveBroadcastException
     */
    public function createFacebookLiveVideo(LiveBroadcast $liveBroadcast, FacebookOutput $facebookOutput)
    {
        $fbSDK = new FacebookSDK([
            'app_id' => $facebookOutput->getApplicationId(),
            'app_secret' => $facebookOutput->getApplicationSecret(),
        ]);

        try {
            $params = array('title' => $liveBroadcast->getName(),
                            'description' => $liveBroadcast->getDescription());

            $fbSDK->setDefaultAccessToken($facebookOutput->getAccessToken());
            $response = $fbSDK->post($facebookOutput->getEntityId().'/live_videos', $params);
        } catch(FacebookResponseException $ex) {
            throw new LiveBroadcastException('Facebook exception: '.$ex->getMessage());
        } catch(FacebookSDKException $ex) {
            throw new LiveBroadcastException('Facebook SDK exception: '.$ex->getMessage());
        }

        $body = $response->getDecodedBody();

        if (array_key_exists('stream_url', $body)) {
            return $body['stream_url'];
        }
    }
}