<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Facebook\Authentication\AccessToken;
use Facebook\Authentication\OAuth2Client;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook as FacebookSDK;
use Facebook\FacebookResponse;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use PHPUnit\Framework\TestCase;

/**
 * Class FacebookApiServiceTest
 */
class FacebookApiServiceTest extends TestCase
{
    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * Test creating a live event
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastOutputException
     */
    public function testCreateLiveEvent(): void
    {
        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime());
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('Test Broadcast');
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getDescription')
            ->willReturn('Test broadcast description');

        $channelFacebook = $this->createMock(ChannelFacebook::class);
        $channelFacebook->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('aToken');

        $response = $this->createMock(FacebookResponse::class);
        $response->expects(self::atLeastOnce())
            ->method('getDecodedBody')
            ->willReturn([
                'stream_url' => 'rtmp://some.url',
                'id' => '2132',
            ]);

        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willReturn(true);
        $sdk->expects(self::atLeastOnce())
            ->method('post')
            ->willReturn($response);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('persist')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->createLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * Test creating a stream on a non-facebook channel
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws LiveBroadcastOutputException
     */
    public function testCreateStreamOnNoChannel(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $channelYouTube = $this->createMock(ChannelYouTube::class);

        $liveBroadcast->expects(self::never())
            ->method('getStartTimestamp');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->createLiveEvent($liveBroadcast, $channelYouTube);
    }

    /**
     * Test that an exception is thrown when no stream can be created
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testExceptionWhenStreamCannotBeCreated(): void
    {
        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime());
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('Test Broadcast');
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getDescription')
            ->willReturn('Test broadcast description');

        $channelFacebook = $this->createMock(ChannelFacebook::class);
        $channelFacebook->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('aToken');

        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willReturn(true);
        $sdk->expects(self::atLeastOnce())
            ->method('post')
            ->willThrowException(new FacebookSDKException('error'));

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->createLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * Test updating a stream on a non-facebook channel
     *
     * @throws LiveBroadcastOutputException
     */
    public function testUpdateStreamOnNoChannel(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $channelYouTube = $this->createMock(ChannelYouTube::class);

        $liveBroadcast->expects(self::never())
            ->method('getStartTimestamp');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->updateLiveEvent($liveBroadcast, $channelYouTube);
    }

    /**
     * Test updating a stream on a non-facebook channel
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testUpdateStreamNoResponse(): void
    {
        $channelFacebook = $this->createMock(ChannelFacebook::class);
        $channelFacebook->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('abc');

        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willThrowException(new FacebookSDKException('wrong token'));

        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime());
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('An updated stream');
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getDescription')
            ->willReturn('Updating the stream');

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('101');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn($streamEvent);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->updateLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * @throws LiveBroadcastOutputException
     */
    public function testUpdateStream(): void
    {
        $response = $this->createMock(FacebookResponse::class);

        $channelFacebook = $this->createMock(ChannelFacebook::class);
        $channelFacebook->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('abc');

        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willReturn(true);
        $sdk->expects(self::atLeastOnce())
            ->method('post')
            ->willReturn($response);

        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime());
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('An updated stream');
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getDescription')
            ->willReturn('Updating the stream');

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('101');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn($streamEvent);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->updateLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * Test that a non-facebook event doesn't get removed
     *
     * @throws LiveBroadcastOutputException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testRemoveLiveEventOnNonFacebookChannel(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $channelYouTube = $this->createMock(ChannelYouTube::class);

        $this->entityManager->expects(self::never())
            ->method('getRepository');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->removeLiveEvent($liveBroadcast, $channelYouTube);
    }

    /**
     * Test getting no event for the stream
     *
     * @throws LiveBroadcastOutputException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testRemoveLiveEventOnNonEvent(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $channelFacebook = $this->createMock(ChannelFacebook::class);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $channelFacebook->expects(self::never())
            ->method('getAccessToken');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->removeLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * Test that a facebook error is caught when removing an event
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testRemoveLiveEventFacebookError(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willThrowException(new FacebookSDKException('invalid token'));

        $liveBroadcast = $this->createMock(LiveBroadcast::class);

        $channelFacebook = $this->createMock(ChannelFacebook::class);
        $channelFacebook->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('token');

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('98321');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn($streamEvent);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->removeLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * Test removing a live event
     *
     * @throws LiveBroadcastOutputException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testRemoveLiveEvent(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willReturn(true);
        $sdk->expects(self::atLeastOnce())
            ->method('delete')
            ->willReturn(true);

        $liveBroadcast = $this->createMock(LiveBroadcast::class);

        $channelFacebook = $this->createMock(ChannelFacebook::class);
        $channelFacebook->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('token');

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('98321');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn($streamEvent);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('remove')
            ->willReturn(true);
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush')
            ->willReturn(true);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->removeLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * @throws LiveBroadcastOutputException
     */
    public function testGetLongLivedAccessTokenWithoutToken(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::never())
            ->method('getOAuth2Client');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->getLongLivedAccessToken(false);
    }

    /**
     * Test that an SDK exception is caught
     *
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testGetLongLivedAccessTokenSdkError(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('getOAuth2Client')
            ->willThrowException(new FacebookSDKException('no such client'));

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->getLongLivedAccessToken('abcdef');
    }

    /**
     * @throws LiveBroadcastOutputException
     */
    public function testGetLongLivedAccessToken(): void
    {
        $accessToken = $this->createMock(AccessToken::class);

        $client = $this->createMock(OAuth2Client::class);
        $client->expects(self::atLeastOnce())
            ->method('getLongLivedAccessToken')
            ->willReturn($accessToken);

        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('getOAuth2Client')
            ->willReturn($client);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->getLongLivedAccessToken('ddadsa');
    }



    /**
     * Test retrieving the app id
     */
    public function testGetAppId(): void
    {
        self::assertEquals('appid', $this->getFacebookApiService()->getAppId());
    }

    /**
     * @return FacebookApiService
     */
    protected function getFacebookApiService(): FacebookApiService
    {
        return new FacebookApiService('appid', 'appsecret', $this->entityManager);
    }

    /**
     * Setup mock object
     */
    protected function setUp()
    {
        $this->entityManager = $this->createMock(EntityManager::class);
    }
}
