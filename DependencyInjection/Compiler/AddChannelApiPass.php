<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddChannelApiPass
 */
class AddChannelApiPass implements CompilerPassInterface
{
    public const CHANNEL_STACK_SERVICE = 'live.broadcast.channelapi.stack';
    public const API_SERVICE_TAG = 'live.broadcast.api';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::CHANNEL_STACK_SERVICE)) {
            return;
        }

        $definition = $container->findDefinition(self::CHANNEL_STACK_SERVICE);
        $taggedServices = $container->findTaggedServiceIds(self::API_SERVICE_TAG);
        $serviceIds = array_keys($taggedServices);

        foreach ($serviceIds as $id) {
            $definition->addMethodCall(
                'addApi',
                [new Reference($id)]
            );
        }
    }
}
