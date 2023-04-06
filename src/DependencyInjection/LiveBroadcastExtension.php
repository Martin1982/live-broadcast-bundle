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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yml');
        $loader->load('services-input.yml');
        $loader->load('services-output.yml');

        $container->setParameter('livebroadcast.config', $config);

        $container->setParameter('livebroadcast.fb.app_id', $config['facebook']['application_id']);
        $container->setParameter('livebroadcast.fb.app_secret', $config['facebook']['application_secret']);

        $container->setParameter('livebroadcast.yt.client_id', $config['youtube']['client_id']);
        $container->setParameter('livebroadcast.yt.client_secret', $config['youtube']['client_secret']);
        $container->setParameter('livebroadcast.yt.redirect_route', $config['youtube']['redirect_route']);

        $container->setParameter('livebroadcast.ffmpeg.log_directory', $config['ffmpeg']['log_directory']);
        $container->setParameter('livebroadcast.thumbnail.web_path', $config['thumbnail']['web_path']);
        $container->setParameter('livebroadcast.thumbnail.upload_directory', $config['thumbnail']['upload_directory']);

        $container->setParameter('livebroadcast.event_loop.timer', $config['event_loop']['timer']);
    }
}
