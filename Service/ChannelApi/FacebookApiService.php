<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookSDK;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlannedChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var FacebookSDK
     */
    private $facebookSDK;

    /**
     * FacebookApiService constructor.
     *
     * @param EntityManager $entityManager
     * @param string|null   $applicationId
     * @param string|null   $applicationSecret
     */
    public function __construct(EntityManager $entityManager, $applicationId = null, $applicationSecret = null)
    {
        $this->applicationId = (string) $applicationId;
        $this->applicationSecret = (string) $applicationSecret;
        $this->entityManager = $entityManager;
    }

    /**
     * @param LiveBroadcast                   $broadcast
     * @param AbstractChannel|ChannelFacebook $channel
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastOutputException
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
                'planned_start_time' => $startTimestamp->format('U'),
                'status' => 'SCHEDULED_LIVE',
            ];

            $this->facebookSDK->setDefaultAccessToken($channel->getAccessToken());
            $response = $this->facebookSDK->post($channel->getFbEntityId().'/live_videos', $params);
        } catch (FacebookSDKException $exception) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK exception: %s', $exception->getMessage()));
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
     * @throws LiveBroadcastOutputException
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
                'status' => 'SCHEDULED_LIVE',
            ];

            $this->facebookSDK->setDefaultAccessToken($channel->getAccessToken());
            $this->facebookSDK->post('/'.$eventId, $params);
        } catch (FacebookSDKException $exception) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK exception: %s', $exception->getMessage()));
        }
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastOutputException
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
        } catch (FacebookSDKException $exception) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK exception: %s', $exception->getMessage()));
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();
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
        $this->ensureSdkLoaded();

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
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastOutputException
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
        } catch (FacebookSDKException $exception) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK exception: %s', $exception->getMessage()));
        }

        return $streamUrl;
    }

    /**
     * @param PlannedChannelInterface $channel
     * @param string|int              $externalId
     *
     * @throws LiveBroadcastOutputException
     */
    public function sendEndSignal(PlannedChannelInterface $channel, $externalId): void
    {
        $this->ensureSdkLoaded();

        if (!$channel instanceof ChannelFacebook) {
            return;
        }

        try {
            $this->facebookSDK->post('/'.$externalId, ['end_live_video' => true]);
        } catch (FacebookSDKException $exception) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK exception: %s', $exception->getMessage()));
        }
    }

    /**
     * Test if the API allows streaming
     *
     * @param AbstractChannel $channel
     *
     * @return bool
     *
     * @throws LiveBroadcastOutputException
     */
    public function canStream(AbstractChannel $channel): bool
    {
        if (!$channel instanceof ChannelFacebook) {
            throw new LiveBroadcastOutputException('Expected Facebook channel');
        }

        $this->ensureSdkLoaded();

        try {
            $this->facebookSDK->setDefaultAccessToken($channel->getAccessToken());
            $this->facebookSDK->get('/'.$channel->getFbEntityId().'/live_videos');
        } catch (FacebookSDKException $exception) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK exception: %s', $exception->getMessage()));
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
     * @throws LiveBroadcastOutputException
     */
    private function initFacebook(): void
    {
        if (empty($this->applicationId) || empty($this->applicationSecret)) {
            throw new LiveBroadcastOutputException('The Facebook application settings are not correct.');
        }

        try {
            $this->setFacebookSdk(new FacebookSDK([
                'app_id' => $this->applicationId,
                'app_secret' => $this->applicationSecret,
                'default_graph_version' => 'v3.3',
            ]));
        } catch (FacebookSDKException $ex) {
            throw new LiveBroadcastOutputException(sprintf('Facebook SDK Exception: %s', $ex->getMessage()));
        }
    }

    /**
     * @throws LiveBroadcastOutputException
     */
    private function ensureSdkLoaded(): void
    {
        if (!$this->facebookSDK) {
            $this->initFacebook();
        }
    }
}
