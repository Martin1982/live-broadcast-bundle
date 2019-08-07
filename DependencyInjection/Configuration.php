<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('live_broadcast');
        $rootNode = $treeBuilder->getRootNode();

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
                ->arrayNode('ffmpeg')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('log_directory')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('thumbnail')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('upload_directory')->defaultValue('/tmp')->end()
                        ->scalarNode('web_path')->defaultValue('/uploads/thumbnails')->end()
                    ->end()
                ->end()
                ->arrayNode('event_loop')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('timer')->defaultValue(5)->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
