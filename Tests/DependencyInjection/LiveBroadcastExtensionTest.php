<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\DependencyInjection;

use Martin1982\LiveBroadcastBundle\DependencyInjection\LiveBroadcastExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class LiveBroadcastExtensionTest
 */
class LiveBroadcastExtensionTest extends TestCase
{
    /**
     * Test configuration file load
     *
     * @throws \Exception
     */
    public function testConfigLoad(): void
    {
        $container = new ContainerBuilder();
        $config = [
            'live_broadcast' => [
                'facebook' => [
                    'application_id' => 'fb_id',
                    'application_secret' => 'fb_secret',
                ],
                'youtube' => [
                    'client_id' => 'yt_id',
                    'client_secret' => 'yt_secret',
                    'redirect_route' => 'yt_redirect',
                ],
                'ffmpeg' => [
                    'log_directory' => 'log_dir',
                ],
                'thumbnail' => [
                    'web_path' => 'thumb_web',
                    'upload_directory' => 'thumb_upload',
                ],
                'eventloop' => [
                    'timer' => 3,
                ],
            ],
        ];

        $extension = new LiveBroadcastExtension();
        $extension->load($config, $container);

        self::assertEquals('fb_id', $container->getParameter('livebroadcast.fb.appid'));
        self::assertEquals('fb_secret', $container->getParameter('livebroadcast.fb.appsecret'));

        self::assertEquals('yt_id', $container->getParameter('livebroadcast.yt.clientid'));
        self::assertEquals('yt_secret', $container->getParameter('livebroadcast.yt.clientsecret'));
        self::assertEquals('yt_redirect', $container->getParameter('livebroadcast.yt.redirectroute'));

        self::assertEquals('log_dir', $container->getParameter('livebroadcast.ffmpeg.logdirectory'));

        self::assertEquals('thumb_web', $container->getParameter('livebroadcast.thumbnail.web_path'));
        self::assertEquals('thumb_upload', $container->getParameter('livebroadcast.thumbnail.uploaddirectory'));

        self::assertEquals(3, $container->getParameter('livebroadcast.eventloop.timer'));
    }
}
