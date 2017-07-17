<?php

namespace Martin1982\LiveBroadcastBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class LiveBroadcastExtension
 * @package Martin1982\LiveBroadcastBundle\DependencyInjection
 */
class LiveBroadcastExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
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
        $container->setParameter('livebroadcast.thumbnail.uploaddirectory', $config['thumbnail']['upload_directory']);
    }
}
