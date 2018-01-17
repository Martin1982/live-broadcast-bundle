<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\BaseMedia;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseMediaTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Media
 */
class BaseMediaTest extends TestCase
{
    /**
     *
     */
    public function testToString()
    {
        $media = $this->getMockForAbstractClass(BaseMedia::class);
        self::assertEmpty((string) $media);

        $reflection = new \ReflectionClass($media);
        $property = $reflection->getProperty('inputId');
        $property->setAccessible(true);
        $property->setValue($media, 5);

        self::assertEquals(5, (string) $media);
    }
}
