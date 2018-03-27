<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddStreamOutputPass
 */
class AddStreamOutputPass implements CompilerPassInterface
{
    public const STREAM_OUTPUT_SERVICE = 'live.broadcast.streamoutput.service';
    public const STREAM_OUTPUT_TAG = 'live.broadcast.output';

    /**
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::STREAM_OUTPUT_SERVICE)) {
            return;
        }

        $definition = $container->findDefinition(self::STREAM_OUTPUT_SERVICE);
        $taggedServices = $container->findTaggedServiceIds(self::STREAM_OUTPUT_TAG);

        /**
         * @var string|int $id
         * @var array      $tags
         */
        foreach ($taggedServices as $id => $tags) {
            $id = (string) $id;
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addStreamOutput',
                    [new Reference($id), $attributes['platform']]
                );
            }
        }
    }
}
