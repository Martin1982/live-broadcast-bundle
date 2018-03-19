<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class LiveBroadcastExtension
 */
class LiveBroadcastExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('livebroadcast.config', $config);

        $container->setParameter('livebroadcast.fb.appid', $config['facebook']['application_id']);
        $container->setParameter('livebroadcast.fb.appsecret', $config['facebook']['application_secret']);

        $container->setParameter('livebroadcast.yt.clientid', $config['youtube']['client_id']);
        $container->setParameter('livebroadcast.yt.clientsecret', $config['youtube']['client_secret']);
        $container->setParameter('livebroadcast.yt.redirectroute', $config['youtube']['redirect_route']);

        $container->setParameter('livebroadcast.ffmpeg.logdirectory', $config['ffmpeg']['log_directory']);
        $container->setParameter('livebroadcast.thumbnail.web_path', $config['thumbnail']['web_path']);
        $container->setParameter('livebroadcast.thumbnail.uploaddirectory', $config['thumbnail']['upload_directory']);

        $container->setParameter('livebroadcast.eventloop.timer', $config['eventloop']['timer']);
    }
}
