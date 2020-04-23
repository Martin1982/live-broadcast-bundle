<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaUrl;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputUrl;
use PHPUnit\Framework\TestCase;

/**
 * Class InputUrlTest
 */
class InputUrlTest extends TestCase
{
    /**
     * @var InputUrl
     */
    private $inputUrl;

    /**
     *
     */
    public function setUp(): void
    {
        $this->inputUrl = new InputUrl();

        $media = new MediaUrl();
        $media->setUrl('http://live.broadcast.com');
        $this->inputUrl->setMedia($media);
    }

    /**
     * Test invalid url as cmd input
     */
    public function testGenerateInputCmdInvalidUrl(): void
    {
        $this->expectException(LiveBroadcastInputException::class);
        $media = new MediaUrl();
        $media->setUrl('invalid_url');
        $this->inputUrl->setMedia($media);

        $this->inputUrl->generateInputCmd();
    }


    /**
     * Test generating an input command
     *
     * @throws LiveBroadcastInputException
     */
    public function testGenerateInputCmd(): void
    {
        self::assertEquals('-re -i \'http://live.broadcast.com\'', $this->inputUrl->generateInputCmd());
    }

    /**
     * Test the media type
     */
    public function testMediaType(): void
    {
        self::assertEquals(MediaUrl::class, $this->inputUrl->getMediaType());
    }
}
