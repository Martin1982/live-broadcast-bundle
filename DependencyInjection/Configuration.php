<?php

namespace Martin1982\LiveBroadcastBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Martin1982\LiveBroadcastBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('live_broadcast');

        $this->addTwitchConfig($rootNode);

        return $treeBuilder;
    }

    /**
     * Set Twitch configuration nodes.
     *
     * @param ArrayNodeDefinition $rootNode
     */
    protected function addTwitchConfig(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('twitch')
                    ->children()
                        ->scalarNode('stream_server_fqdn')
                            ->defaultValue('live.twitch.tv')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('stream_key')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
