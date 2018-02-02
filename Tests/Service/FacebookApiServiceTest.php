<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Service\FacebookApiService;
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
    public function testGetAppId()
    {
        self::assertEquals('appid', $this->facebook->getAppId());
    }

    /**
     * Setup mock object
     */
    protected function setUp()
    {
        $this->facebook = new FacebookApiService('appid', 'appsecret');
    }
}
