<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Service;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookAPI;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
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
     */
    public function createFacebookLiveVideo(LiveBroadcast $liveBroadcast, FacebookOutput $facebookOutput)
    {
        $fbAPI = new FacebookAPI([
            'app_id' => $facebookOutput->getApplicationId(),
            'app_secret' => $facebookOutput->getApplicationSecret(),
        ]);

        try {
            $params = array('title' => $liveBroadcast->getName(),
                            'description' => $liveBroadcast->getDescription());

            $response = $fbAPI->post($facebookOutput->getEntityId().'/live_videos', $params);
        } catch(FacebookResponseException $ex) {
            echo 'Graph returned an error: ' . $ex->getMessage();
            exit;
        } catch(FacebookSDKException $ex) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $ex->getMessage();
            exit;
        }

        var_dump($response->getDecodedBody());
    }
}