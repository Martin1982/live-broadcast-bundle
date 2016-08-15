<?php

namespace Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class AddStreamInputPass implements CompilerPassInterface
{
    const STREAM_INPUT_SERVICE = 'live.broadcast.streaminput.service';
    const STREAM_INPUT_TAG = 'live.broadcast.input';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::STREAM_INPUT_SERVICE)) {
            return;
        }

        $definition = $container->findDefinition(self::STREAM_INPUT_SERVICE);
        $taggedServices = $container->findTaggedServiceIds(self::STREAM_INPUT_TAG);

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addStreamInput',
                    array(new Reference($id), $attributes['inputtype'])
                );
            }
        }
    }
}
