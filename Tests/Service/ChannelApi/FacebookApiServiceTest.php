<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use PHPUnit\Framework\TestCase;

/**
 * Class FacebookApiServiceTest
 */
class FacebookApiServiceTest extends TestCase
{
    /**
     * @var FacebookApiService
     */
    protected $facebook;

    /**
     * Test retrieving the app id
     */
    public function testGetAppId(): void
    {
        self::assertEquals('appid', $this->facebook->getAppId());
    }

    /**
     * Setup mock object
     */
    protected function setUp()
    {
        $entityManager = $this->createMock(EntityManager::class);

        $this->facebook = new FacebookApiService('appid', 'appsecret', $entityManager);
    }
}
