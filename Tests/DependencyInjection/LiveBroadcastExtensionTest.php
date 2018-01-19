<?php

namespace Martin1982\LiveBroadcastBundle\Tests\DependencyInjection\Compiler;

use Martin1982\LiveBroadcastBundle\DependencyInjection\LiveBroadcastExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class LiveBroadcastExtensionTest
 * @package Martin1982\LiveBroadcastBundle\Tests\DependencyInjection
 */
class LiveBroadcastExtensionTest extends TestCase
{
    /**
     * Test configuration file load
     *
     * @throws \Exception
     */
    public function testConfigLoad()
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
                    'upload_directory' => 'thumb_upload'
                ],
                'eventloop' => [
                    'enabled' => true,
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

        self::assertTrue($container->getParameter('livebroadcast.eventloop.enabled'));
        self::assertEquals(3, $container->getParameter('livebroadcast.eventloop.timer'));
    }
}
