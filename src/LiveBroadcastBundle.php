<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle;

use Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler\AddChannelApiPass;
use Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler\AddStreamInputPass;
use Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler\AddStreamOutputPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class LiveBroadcastBundle.
 */
class LiveBroadcastBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new AddStreamOutputPass());
        $container->addCompilerPass(new AddStreamInputPass());
        $container->addCompilerPass(new AddChannelApiPass());
    }
}
