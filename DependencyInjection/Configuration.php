<?php

namespace Martin1982\LiveBroadcastBundle\DependencyInjection;

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

        $rootNode
            ->children()
                ->arrayNode('facebook')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('application_id')->defaultNull()->end()
                        ->scalarNode('application_secret')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('youtube')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('client_id')->defaultNull()->end()
                        ->scalarNode('client_secret')->defaultNull()->end()
                        ->scalarNode('redirect_route')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
