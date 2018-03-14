<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\DependencyInjection\Compiler;

use Martin1982\LiveBroadcastBundle\DependencyInjection\Compiler\AddStreamInputPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddStreamInputPassTest
 */
class AddStreamInputPassTest extends TestCase
{
    /**
     * @var AddStreamInputPass
     */
    private $compilerPass;

    /**
     *
     */
    public function setUp()
    {
        $this->compilerPass = new AddStreamInputPass();
    }

    /**
     * Test that no processing takes place when the service isn't named correctly
     */
    public function testNoProcessing()
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects(static::once())
            ->method('hasDefinition')
            ->with('live.broadcast.streaminput.service')
            ->willReturn(false);

        $container->expects(static::never())
            ->method('findDefinition');

        $this->compilerPass->process($container);
    }

    /**
     * Test processing tagged services
     */
    public function testProcess()
    {
        /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        $definition = $this->createMock(Definition::class);

        $container->expects(static::once())
            ->method('hasDefinition')
            ->with('live.broadcast.streaminput.service')
            ->willReturn(true);

        $container->expects(static::once())
            ->method('findDefinition')
            ->with('live.broadcast.streaminput.service')
            ->willReturn($definition);

        $container->expects(static::once())
            ->method('findTaggedServiceIds')
            ->with('live.broadcast.input')
            ->willReturn([['live.broadcast.input.file' => ['inputtype' => 'File']]]);

        $definition->expects(static::once())
            ->method('addMethodCall')
            ->with('addStreamInput', [new Reference('0'), 'File']);

        $this->compilerPass->process($container);
    }
}
