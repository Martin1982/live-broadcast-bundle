<?php

namespace Martin1982\LiveBroadcastBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
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
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $processor = new Processor();
        $configuration = $this->getConfiguration($configs, $container);
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('live_broadcast.twitch.stream_server_fqdn', $config['twitch']['stream_server_fqdn']);
        $container->setParameter('live_broadcast.twitch.stream_key', $config['twitch']['stream_key']);
        $container->setParameter('live_broadcast.symfony_environment', $container->getParameter("kernel.environment"));
    }
}
