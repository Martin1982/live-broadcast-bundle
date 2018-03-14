<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Event;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractBroadcastEventTest
 */
class AbstractBroadcastEventTest extends TestCase
{
    /**
     * Test getting properties
     */
    public function testProperties()
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $output = $this->createMock(OutputInterface::class);

        $testClass = new PostBroadcastEvent($broadcast, $output);
        self::assertEquals($broadcast, $testClass->getLiveBroadcast());
        self::assertEquals($output, $testClass->getOutput());
    }
}
