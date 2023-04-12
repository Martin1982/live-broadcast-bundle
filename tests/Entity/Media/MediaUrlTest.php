<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaUrl;
use PHPUnit\Framework\TestCase;

/**
 * Class MediaUrlTest
 */
class MediaUrlTest extends TestCase
{
    /**
     * Test the getUrl method
     */
    public function testGetUrl(): void
    {
        $input = new MediaUrl();
        self::assertEquals('', $input->getUrl());

        $input->setUrl('https://www.google.com');
        self::assertEquals('https://www.google.com', $input->getUrl());
    }

    /**
     * Test the __toString method
     */
    public function testToString(): void
    {
        $input = new MediaUrl();
        self::assertEquals('', (string) $input);

        $input->setUrl('https://github.com/Martin1982/live-broadcast-bundle');
        self::assertEquals('https://github.com/Martin1982/live-broadcast-bundle', $input->getUrl());
    }
}
