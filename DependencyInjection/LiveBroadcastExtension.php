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

        $container->setParameter('fb_app_id', $config['facebook']['application_id']);
        $container->setParameter('fb_app_secret', $config['facebook']['application_secret']);
        $container->setParameter('yt_client_id', $config['youtube']['client_id']);
        $container->setParameter('yt_client_secret', $config['youtube']['client_secret']);
        $container->setParameter('yt_redirect_route', $config['youtube']['redirect_route']);
    }
}
