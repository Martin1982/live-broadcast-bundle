<?php

namespace Martin1982\LiveBroadcastBundle\Tests;

use Martin1982\LiveBroadcastBundle\LiveBroadcastBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class LiveBroadcastBundleTest
 */
class LiveBroadcastBundleTest extends \PHPUnit_Framework_TestCase
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
    }
}
