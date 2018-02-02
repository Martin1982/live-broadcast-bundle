<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Service\FacebookApiService;

/**
 * Class FacebookApiServiceTest
 */
class FacebookApiServiceTest extends \PHPUnit_Framework_TestCase
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
