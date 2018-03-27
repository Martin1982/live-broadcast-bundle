<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\DependencyInjection\Compiler;

use Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler\AddChannelApiPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddChannelApiPassTest
 */
class AddChannelApiPassTest extends TestCase
{
    /**
     * @var AddChannelApiPass
     */
    private $compilerPass;

    /**
     * Setup basic object
     */
    public function setUp()
    {
        $this->compilerPass = new AddChannelApiPass();
    }

    /**
     * Test that no processing takes place when the service isn't named correctly
     */
    public function testNoProcessing(): void
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects(static::once())
            ->method('hasDefinition')
            ->with('live.broadcast.channelapi.stack')
            ->willReturn(false);

        $container->expects(static::never())
            ->method('findDefinition');

        $this->compilerPass->process($container);
    }

    /**
     * Test processing tagged services
     */
    public function testProcess(): void
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        $definition = $this->createMock(Definition::class);

        $container->expects(static::once())
            ->method('hasDefinition')
            ->with('live.broadcast.channelapi.stack')
            ->willReturn(true);

        $container->expects(static::once())
            ->method('findDefinition')
            ->with('live.broadcast.channelapi.stack')
            ->willReturn($definition);

        $container->expects(static::once())
            ->method('findTaggedServiceIds')
            ->with('live.broadcast.api')
            ->willReturn([
                [
                    'live.broadcast.api.facebook' => [],
                ],
            ]);

        $definition->expects(static::once())
            ->method('addMethodCall')
            ->with('addApi', [new Reference('0')]);

        $this->compilerPass->process($container);
    }
}
