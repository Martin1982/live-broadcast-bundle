<?php

namespace Martin1982\LiveBroadcastBundle;

use Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler\AddStreamInputPass;
use Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler\AddStreamOutputPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class LiveBroadcastBundle
 * @package Martin1982\LiveBroadcastBundle
 */
class LiveBroadcastBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AddStreamOutputPass());
        $container->addCompilerPass(new AddStreamInputPass());
    }
}
