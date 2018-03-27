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
 * Class AddStreamInputPass
 */
class AddStreamInputPass implements CompilerPassInterface
{
    public const STREAM_INPUT_SERVICE = 'live.broadcast.streaminput.service';
    public const STREAM_INPUT_TAG = 'live.broadcast.input';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::STREAM_INPUT_SERVICE)) {
            return;
        }

        $definition = $container->findDefinition(self::STREAM_INPUT_SERVICE);
        $taggedServices = $container->findTaggedServiceIds(self::STREAM_INPUT_TAG);

        /**
         * @var string|int $id
         * @var array      $tags
         */
        foreach ($taggedServices as $id => $tags) {
            $id = (string) $id;
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'addStreamInput',
                    [new Reference($id), $attributes['inputtype']]
                );
            }
        }
    }
}
