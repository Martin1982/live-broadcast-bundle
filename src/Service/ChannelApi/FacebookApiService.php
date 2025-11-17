<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Facebook\Authentication\AccessToken;
use Facebook\Exception\SDKException;
use Facebook\Facebook as FacebookSDK;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlannedChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastApiException;

/**
 * Class FacebookApiService
 */
class FacebookApiService implements ChannelApiInterface
{
    /**
     * @var FacebookSDK|null
     */
    private ?FacebookSDK $facebookSDK = null;

    /**
     * FacebookApiService constructor.
     *
     * @param EntityManager $entityManager
     * @param string        $applicationId
     * @param string        $applicationSecret
     */
    public function __construct(private EntityManager $entityManager, private string $applicationId, private string $applicationSecret)
    {
    }

    /**
     * @param LiveBroadcast                    $broadcast
     * @param AbstractChannel<ChannelFacebook> $channel
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastApiException
     */
    public function createLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $this->ensureSdkLoaded();

        if (!$channel instanceof ChannelFacebook) {
            return;
        }

        $eventId = null;

        $minTimestamp = new \DateTime('+15 minutes');
        $startTimestamp = $broadcast->getStartTimestamp();

        if ($startTimestamp < $minTimestamp) {
            $startTimestamp = $minTimestamp;
        }

        try {
            $params = [
                'title' => $broadcast->getName(),
                'description' => $broadcast->getDescription(),
                'event_params' => ['start_time' => $startTimestamp->format('U')],
            ];

            $this->facebookSDK->setDefaultAccessToken($channel->getAccessToken());
            $response = $this->facebookSDK->post($channel->getFbEntityId().'/live_videos', $params);
        } catch (SDKException $exception) {
            throw new LiveBroadcastApiException(sprintf('Facebook SDK live_videos post exception: %s', $exception->getMessage()));
        }

        $body = $response->getDecodedBody();

        if (array_key_exists('stream_url', $body)) {
            $eventId = $body['id'];
        }

        $event = new StreamEvent();
        $event->setBroadcast($broadcast);
        $event->setChannel($channel);
        $event->setExternalStreamId($eventId);

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastApiException
     */
    public function updateLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $this->ensureSdkLoaded();

        if (!$channel instanceof ChannelFacebook) {
            return;
        }

        $eventRepository = $this->entityManager->getRepository(StreamEvent::class);
        $event = $eventRepository->findOneBy(compact('broadcast', 'channel'));

        $eventId = $event->getExternalStreamId();

        $minTimestamp = new \DateTime('+15 minutes');
        $startTimestamp = $broadcast->getStartTimestamp();

        if ($startTimestamp < $minTimestamp) {
            $startTimestamp = $minTimestamp;
        }

        try {
            $params = [
                'title' => $broadcast->getName(),
                'description' => $broadcast->getDescription(),
                'planned_start_time' => $startTimestamp->format('U'),
            ];

            $this->facebookSDK->setDefaultAccessToken($channel->getAccessToken());
            $this->facebookSDK->post('/'.$eventId, $params);
        } catch (SDKException $exception) {
            throw new LiveBroadcastApiException(sprintf('Facebook SDK event post exception: %s', $exception->getMessage()));
        }
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastApiException
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $this->ensureSdkLoaded();

        if (!$channel instanceof ChannelFacebook) {
            return;
        }

        $eventRepository = $this->entityManager->getRepository(StreamEvent::class);
        $event = $eventRepository->findOneBy(compact('broadcast', 'channel'));

        if (!$event) {
            return;
        }

        $eventId = $event->getExternalStreamId();
        try {
            $this->facebookSDK->setDefaultAccessToken($channel->getAccessToken());
            $this->facebookSDK->delete('/'.$eventId);
        } catch (SDKException $exception) {
            throw new LiveBroadcastApiException(sprintf('Facebook SDK event delete exception: %s', $exception->getMessage()));
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();
    }

    /**
     * @param string|null $userAccessToken
     *
     * @return AccessToken|null
     *
     * @throws LiveBroadcastApiException
     */
    public function getLongLivedAccessToken(?string $userAccessToken): ?AccessToken
    {
        $this->ensureSdkLoaded();

        if (!$userAccessToken) {
            return null;
        }

        try {
            return $this->facebookSDK->getOAuth2Client()->getLongLivedAccessToken($userAccessToken);
        } catch (SDKException $ex) {
            throw new LiveBroadcastApiException(sprintf('Facebook SDK access token exception: %s', $ex->getMessage()));
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
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastApiException
     */
    public function getStreamUrl(LiveBroadcast $broadcast, AbstractChannel $channel): string
    {
        $this->ensureSdkLoaded();

        if (!$channel instanceof ChannelFacebook) {
            return '';
        }

        $eventRepository = $this->entityManager->getRepository(StreamEvent::class);
        $event = $eventRepository->findOneBy(compact('broadcast', 'channel'));

        if (!$event) {
            return '';
        }

        $eventId = $event->getExternalStreamId();
        try {
            $this->facebookSDK->setDefaultAccessToken($channel->getAccessToken());
            $facebookStream = $this->facebookSDK->get('/'.$eventId);
            $streamUrl = $facebookStream->getGraphNode()->getField('stream_url');
        } catch (SDKException $exception) {
            throw new LiveBroadcastApiException(sprintf('Facebook SDK stream_url get exception: %s', $exception->getMessage()));
        }

        return $streamUrl;
    }

    /**
     * @param PlannedChannelInterface $channel
     * @param int|string              $externalId
     *
     * @throws LiveBroadcastApiException
     */
    public function sendEndSignal(PlannedChannelInterface $channel, int|string $externalId): void
    {
        $this->ensureSdkLoaded();

        if (!$channel instanceof ChannelFacebook) {
            return;
        }

        try {
            $this->facebookSDK->post('/'.$externalId, ['end_live_video' => true]);
        } catch (SDKException $exception) {
            throw new LiveBroadcastApiException(sprintf('Facebook SDK end live video exception: %s', $exception->getMessage()));
        }
    }

    /**
     * Test if the API allows streaming
     *
     * @param AbstractChannel $channel
     *
     * @return bool
     *
     * @throws LiveBroadcastApiException
     */
    public function canStream(AbstractChannel $channel): bool
    {
        if (!$channel instanceof ChannelFacebook) {
            throw new LiveBroadcastApiException('Expected Facebook channel');
        }

        $this->ensureSdkLoaded();

        try {
            $this->facebookSDK->setDefaultAccessToken($channel->getAccessToken());
            $this->facebookSDK->get('/'.$channel->getFbEntityId().'/live_videos');
        } catch (SDKException $exception) {
            throw new LiveBroadcastApiException(sprintf('Facebook SDK get live_videos exception: %s', $exception->getMessage()));
        }

        return true;
    }

    /**
     * @param FacebookSDK $sdk
     */
    public function setFacebookSdk(FacebookSDK $sdk): void
    {
        $this->facebookSDK = $sdk;
    }

    /**
     * @throws LiveBroadcastApiException
     */
    private function initFacebook(): void
    {
        if (empty($this->applicationId) || empty($this->applicationSecret)) {
            throw new LiveBroadcastApiException('The Facebook application settings are not correct.');
        }

        try {
            $this->setFacebookSdk(new FacebookSDK([
                'app_id' => $this->applicationId,
                'app_secret' => $this->applicationSecret,
                'default_graph_version' => 'v16.0',
            ]));
        } catch (SDKException $ex) {
            throw new LiveBroadcastApiException(sprintf('Facebook SDK init exception: %s', $ex->getMessage()));
        }
    }

    /**
     * @throws LiveBroadcastApiException
     */
    private function ensureSdkLoaded(): void
    {
        if (!$this->facebookSDK) {
            $this->initFacebook();
        }
    }
}
