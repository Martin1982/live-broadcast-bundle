<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Facebook\Facebook as FacebookSDK;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
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

        $sdk = $this->createMock(FacebookSDK::class);

        $facebook = $this->getFacebookApiService();
        $facebook->setFacebookSdk($sdk);
        $facebook->createLiveEvent($liveBroadcast, $channelFacebook);
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
