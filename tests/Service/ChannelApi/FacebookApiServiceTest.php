<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use Facebook\Authentication\AccessToken;
use Facebook\Authentication\OAuth2Client;
use Facebook\Exception\SDKException;
use Facebook\Facebook as FacebookSDK;
use Facebook\Response;
use Facebook\GraphNode\GraphNode;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastApiException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class FacebookApiServiceTest
 */
class FacebookApiServiceTest extends TestCase
{
    /**
     * @var EntityManager|MockObject
     */
    protected $entityManager;

    /**
     * Test creating a live event
     *
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testCreateLiveEvent(): void
    {
        $liveBroadcast = $this->getLiveBroadcast();
        $channelFacebook = $this->getFacebookChannel();

        $response = $this->createMock(Response::class);
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
            ->method('persist');
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->createLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * Test creating a stream on a non-facebook channel
     *
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws OptimisticLockException
     * @throws ORMException
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
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws OptimisticLockException
     */
    public function testExceptionWhenStreamCannotBeCreated(): void
    {
        $this->expectException(LiveBroadcastApiException::class);
        $liveBroadcast = $this->getLiveBroadcast();

        $channelFacebook = $this->getFacebookChannel();

        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willReturn(true);
        $sdk->expects(self::atLeastOnce())
            ->method('post')
            ->willThrowException(new SDKException('error'));

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->createLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * Test updating a stream on a non-facebook channel
     *
     * @throws Exception
     * @throws LiveBroadcastApiException
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
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function testUpdateStreamNoResponse(): void
    {
        $this->expectException(LiveBroadcastApiException::class);
        $channelFacebook = $this->createMock(ChannelFacebook::class);
        $channelFacebook->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('abc');

        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willThrowException(new SDKException('wrong token'));

        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new DateTime());
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
     * @throws Exception
     * @throws LiveBroadcastApiException
     */
    public function testUpdateStream(): void
    {
        $response = $this->createMock(Response::class);

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
            ->willReturn(new DateTime());
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
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws OptimisticLockException
     * @throws ORMException
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
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws OptimisticLockException
     * @throws ORMException
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
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testRemoveLiveEventFacebookError(): void
    {
        $this->expectException(LiveBroadcastApiException::class);
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willThrowException(new SDKException('invalid token'));

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
     * @throws Exception
     * @throws LiveBroadcastApiException
     * @throws OptimisticLockException
     * @throws ORMException
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
            ->method('remove');
        $this->entityManager->expects(self::atLeastOnce())
            ->method('flush');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->removeLiveEvent($liveBroadcast, $channelFacebook);
    }

    /**
     * @throws Exception
     * @throws LiveBroadcastApiException
     */
    public function testGetLongLivedAccessTokenWithoutToken(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::never())
            ->method('getOAuth2Client');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->getLongLivedAccessToken('');
    }

    /**
     * Test that an SDK exception is caught
     *
     * @throws Exception
     */
    public function testGetLongLivedAccessTokenSdkError(): void
    {
        $this->expectException(LiveBroadcastApiException::class);
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('getOAuth2Client')
            ->willThrowException(new SDKException('no such client'));

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->getLongLivedAccessToken('a_token');
    }

    /**
     * @throws Exception
     * @throws LiveBroadcastApiException
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
        $facebook->getLongLivedAccessToken('token');
    }

    /**
     * Test that this method doesn't do anything with an incompatible channel
     *
     * @throws Exception
     * @throws LiveBroadcastApiException
     */
    public function testGetStreamUrlWithNonFacebookChannel(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelYouTube::class);

        $this->entityManager->expects(self::never())
            ->method('getRepository');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $url = $facebook->getStreamUrl($broadcast, $channel);

        self::assertEmpty($url);
    }

    /**
     * @throws Exception
     * @throws LiveBroadcastApiException
     */
    public function testGetStreamUrlWithNoEvent(): void
    {
        $sdk = $this->createMock(FacebookSDK::class);
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(ChannelFacebook::class);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $url = $facebook->getStreamUrl($broadcast, $channel);

        self::assertEmpty($url);
    }

    /**
     * Test that a facebook exception is caught
     *
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function testGetStreamUrlFacebookError(): void
    {
        $this->expectException(LiveBroadcastApiException::class);
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willThrowException(new SDKException('no token'));

        $broadcast = $this->createMock(LiveBroadcast::class);

        $channel = $this->createMock(ChannelFacebook::class);
        $channel->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('access_granted');

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('832');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn($streamEvent);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $url = $facebook->getStreamUrl($broadcast, $channel);

        self::assertEmpty($url);
    }

    /**
     * @throws Exception
     * @throws LiveBroadcastApiException
     */
    public function testGetStreamUrl(): void
    {
        $node = $this->createMock(GraphNode::class);
        $node->expects(self::atLeastOnce())
            ->method('getField')
            ->willReturn('rtmp://some.url');

        $response = $this->createMock(Response::class);
        $response->expects(self::atLeastOnce())
            ->method('getGraphNode')
            ->willReturn($node);

        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('setDefaultAccessToken')
            ->willReturn(true);
        $sdk->expects(self::atLeastOnce())
            ->method('get')
            ->willReturn($response);

        $broadcast = $this->createMock(LiveBroadcast::class);

        $channel = $this->createMock(ChannelFacebook::class);
        $channel->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('access_granted');

        $streamEvent = $this->createMock(StreamEvent::class);
        $streamEvent->expects(self::atLeastOnce())
            ->method('getExternalStreamId')
            ->willReturn('832');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::atLeastOnce())
            ->method('findOneBy')
            ->willReturn($streamEvent);

        $this->entityManager->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturn($repository);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $url = $facebook->getStreamUrl($broadcast, $channel);

        self::assertEquals('rtmp://some.url', $url);
    }

    /**
     * Test sending an end signal to a non Facebook channel
     *
     * @throws Exception
     * @throws LiveBroadcastApiException
     */
    public function testSendEndSignalOnNonFacebookChannel(): void
    {
        $channel = $this->createMock(ChannelYouTube::class);
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::never())
            ->method('post');

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->sendEndSignal($channel, '3223');
    }

    /**
     * Test that an exception is caught and converted
     *
     * @throws Exception
     * @throws Exception
     */
    public function testSendEndSignalSdkError(): void
    {
        $this->expectException(LiveBroadcastApiException::class);
        $channel = $this->createMock(ChannelFacebook::class);
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('post')
            ->willThrowException(new SDKException('no such event'));

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->sendEndSignal($channel, '3223');
    }

    /**
     * Test sending an end signal
     *
     * @throws Exception
     * @throws LiveBroadcastApiException
     */
    public function testSendEndSignal(): void
    {
        $channel = $this->createMock(ChannelFacebook::class);
        $sdk = $this->createMock(FacebookSDK::class);
        $sdk->expects(self::atLeastOnce())
            ->method('post')
            ->willReturn(true);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->sendEndSignal($channel, '3223');
    }

    /**
     * Test initializing without proper parameters
     * @throws Exception
     */
    public function testInitFacebookWithoutParameters(): void
    {
        $this->expectException(LiveBroadcastApiException::class);
        $channel = $this->createMock(ChannelYouTube::class);
        $channel->expects(self::never())
            ->method('getRefreshToken');

        $facebook = new FacebookApiService($this->entityManager, '', '');
        $facebook->sendEndSignal($channel, '3223');
    }

    /**
     * Test initializing Facebook and getting an SDK error
     * @throws Exception
     */
    public function testInitFacebookWithSdkError(): void
    {
        $this->expectException(LiveBroadcastApiException::class);
        $this->expectExceptionMessage('Facebook SDK init exception: Something went wrong...');
        $channel = $this->createMock(ChannelYouTube::class);
        $channel->expects(self::never())
            ->method('getRefreshToken');

        $facebook = new FacebookApiServiceMock($this->entityManager, 'a', 'b');
        $facebook->sendEndSignal($channel, '3223');
    }

    /**
     * @throws LiveBroadcastApiException
     * @throws Exception
     */
    public function testInitFacebook(): void
    {
        $channel = $this->createMock(ChannelYouTube::class);
        $channel->expects(self::never())
            ->method('getRefreshToken');

        $facebook = $this->getFacebookApiService();
        $facebook->sendEndSignal($channel, '3223');
    }

    /**
     * Test retrieving the app id
     */
    public function testGetAppId(): void
    {
        self::assertEquals('app_id', $this->getFacebookApiService()->getAppId());
    }

    /**
     * @return FacebookApiService
     */
    protected function getFacebookApiService(): FacebookApiService
    {
        return new FacebookApiService($this->entityManager, 'app_id', 'app_secret');
    }

    /**
     * Setup mock object
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
    }

    /**
     * Get a general live broadcast object
     *
     * @return LiveBroadcast
     *
     * @throws Exception
     */
    protected function getLiveBroadcast(): LiveBroadcast
    {
        $liveBroadcast = $this->createMock(LiveBroadcast::class);
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new DateTime());
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getName')
            ->willReturn('Test Broadcast');
        $liveBroadcast->expects(self::atLeastOnce())
            ->method('getDescription')
            ->willReturn('Test broadcast description');

        return $liveBroadcast;
    }

    /**
     * Get a Facebook channel mock
     *
     * @return ChannelFacebook
     *
     * @throws Exception
     */
    protected function getFacebookChannel(): ChannelFacebook
    {
        $channelFacebook = $this->createMock(ChannelFacebook::class);
        $channelFacebook->expects(self::atLeastOnce())
            ->method('getAccessToken')
            ->willReturn('aToken');

        return $channelFacebook;
    }
}
