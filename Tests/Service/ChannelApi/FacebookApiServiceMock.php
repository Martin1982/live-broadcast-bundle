<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\ChannelApi;

use Facebook\Exception\SDKException;
use Facebook\Facebook as FacebookSDK;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;

/**
 * Class FacebookApiServiceMock
 */
class FacebookApiServiceMock extends FacebookApiService
{
    /**
     * @param FacebookSDK $sdk
     *
     * @throws SDKException
     */
    public function setFacebookSdk(FacebookSDK $sdk): void
    {
        if ($sdk) {
            throw new SDKException('Something went wrong...');
        }
    }
}
