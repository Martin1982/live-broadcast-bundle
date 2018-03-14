<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractMediaTest
 */
class AbstractMediaTest extends TestCase
{
    /**
     *
     */
    public function testToString()
    {
        $media = $this->getMockForAbstractClass(AbstractMedia::class);
        self::assertEmpty((string) $media);

        $reflection = new \ReflectionClass($media);
        $property = $reflection->getProperty('inputId');
        $property->setAccessible(true);
        $property->setValue($media, 5);

        self::assertEquals(5, (string) $media);
    }
}
