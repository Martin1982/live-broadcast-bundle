<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi;

use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookSDK;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;

/**
 * Class FacebookApiService
 */
class FacebookApiService implements ChannelApiInterface
{
    /**
     * @var string
     */
    private $applicationId;

    /**
     * @var string
     */
    private $applicationSecret;

    /**
     * @var FacebookSDK
     */
    private $facebookSDK;

    /**
     * FacebookApiService constructor.
     * @param string $applicationId
     * @param string $applicationSecret
     */
    public function __construct($applicationId, $applicationSecret)
    {
        $this->applicationId = $applicationId;
        $this->applicationSecret = $applicationSecret;
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     */
    public function createLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel)
    {
        // TODO: Implement createLiveEvent() method.
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     */
    public function updateLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel)
    {
        // TODO: Implement updateLiveEvent() method.
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     */
    public function removeLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel)
    {
        // TODO: Implement removeLiveEvent() method.
    }

    /**
     * @param LiveBroadcast  $liveBroadcast
     * @param OutputFacebook $outputFacebook
     *
     * @return null|string
     *
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastOutputException
     */
    public function createFacebookLiveVideo(LiveBroadcast $liveBroadcast, OutputFacebook $outputFacebook): ?string
    {
        if (!$this->facebookSDK) {
            $this->initFacebook();
        }

        try {
            $params = [
                'title' => $liveBroadcast->getName(),
                'description' => $liveBroadcast->getDescription(),
            ];

            $this->facebookSDK->setDefaultAccessToken($outputFacebook->getAccessToken());
            $response = $this->facebookSDK->post($outputFacebook->getEntityId().'/live_videos', $params);
        } catch (FacebookResponseException $ex) {
            throw new LiveBroadcastOutputException(sprintf('Facebook exception: %s', $ex->getMessage()));
        } catch (FacebookSDKException $ex) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK exception: %s', $ex->getMessage()));
        }

        $body = $response->getDecodedBody();

        if (array_key_exists('stream_url', $body)) {
            return $body['stream_url'];
        }

        return null;
    }

    /**
     * @param string $userAccessToken
     *
     * @return AccessToken|null
     *
     * @throws LiveBroadcastOutputException
     */
    public function getLongLivedAccessToken($userAccessToken): ?AccessToken
    {
        if (!$this->facebookSDK) {
            $this->initFacebook();
        }

        if (!$userAccessToken) {
            return null;
        }

        try {
            return $this->facebookSDK->getOAuth2Client()->getLongLivedAccessToken($userAccessToken);
        } catch (FacebookSDKException $ex) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK exception: %s', $ex->getMessage()));
        }
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->applicationId;
    }

    /**
     * @throws LiveBroadcastOutputException
     */
    private function initFacebook(): void
    {
        if (empty($this->applicationId) || empty($this->applicationSecret)) {
            throw new LiveBroadcastOutputException('The Facebook application settings are not correct.');
        }

        try {
            $this->facebookSDK = new FacebookSDK([
                'app_id' => $this->applicationId,
                'app_secret' => $this->applicationSecret,
            ]);
        } catch (FacebookSDKException $ex) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK Exception: %s', $ex->getMessage()));
        }
    }
}
