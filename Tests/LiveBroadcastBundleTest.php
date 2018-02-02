<?php

namespace Martin1982\LiveBroadcastBundle\Tests;

use Martin1982\LiveBroadcastBundle\LiveBroadcastBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class LiveBroadcastBundleTest
 */
class LiveBroadcastBundleTest extends TestCase
{
    /**
     * Test building the bundle
     */
    public function testBuild()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects(self::any())
            ->method('addCompilerPass')
            ->willReturn(true);

        $bundle = new LiveBroadcastBundle();

        $bundle->build($container);
        $this->addToAssertionCount(1);
    }
}
