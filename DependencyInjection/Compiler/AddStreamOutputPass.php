<?php

namespace Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddStreamOutputPass
 * @package Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler
 */
class AddStreamOutputPass implements CompilerPassInterface
{
    const STREAM_OUTPUT_SERVICE = 'live.broadcast.streamoutput.service';
    const STREAM_OUTPUT_TAG = 'live.broadcast.output';

    /**
     * @param ContainerBuilder $container
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::STREAM_OUTPUT_SERVICE)) {
            return;
        }

        $definition = $container->findDefinition(self::STREAM_OUTPUT_SERVICE);
        $taggedServices = $container->findTaggedServiceIds(self::STREAM_OUTPUT_TAG);

        /**
         * @var string $id
         * @var array  $tags
         */
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addStreamOutput',
                    [new Reference($id), $attributes['platform']]
                );
            }
        }
    }
}
