<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Entity;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class LiveBroadcastTest
 */
class LiveBroadcastTest extends TestCase
{
    /**
     * Test class instance.
     */
    public function testClass(): void
    {
        $broadcast = new LiveBroadcast();
        self::assertInstanceOf(LiveBroadcast::class, $broadcast);
    }

    /**
     * Test get methods
     */
    public function testGetMethods(): void
    {
        $now = new \DateTime('+15 minutes');
        $endTime = new \DateTime('+1 hour');

        $channelOne = $this->createMock(AbstractChannel::class);
        $channelTwo = $this->createMock(AbstractChannel::class);

        $channels = [
            $channelOne,
            $channelTwo,
        ];

        $broadcast = new LiveBroadcast();
        $broadcast->setName('Test');
        $broadcast->setDescription('Description of broadcast');
        $broadcast->setThumbnail(new File('test', false));
        $broadcast->setOutputChannels($channels);
        $broadcast->setInput($this->createMock(AbstractMedia::class));

        self::assertEquals('-', (string) $broadcast);
        self::assertNull($broadcast->getBroadcastId());
        self::assertEquals($now->format('Y-m-d H:i:s'), $broadcast->getStartTimestamp()->format('Y-m-d H:i:s'));
        self::assertEquals($endTime->format('Y-m-d H:i:s'), $broadcast->getEndTimestamp()->format('Y-m-d H:i:s'));
        self::assertCount(2, $broadcast->getOutputChannels());
        self::assertEquals('Test', $broadcast->getName());
        self::assertEquals('Description of broadcast', $broadcast->getDescription());
        self::assertInstanceOf(File::class, $broadcast->getThumbnail());
        self::assertInstanceOf(AbstractChannel::class, $broadcast->getOutputChannels()[0]);
        $broadcast->removeOutputChannel($channelTwo);
        self::assertCount(1, $broadcast->getOutputChannels());
        self::assertInstanceOf(AbstractMedia::class, $broadcast->getInput());

        /* Check default value */
        self::assertTrue($broadcast->isStopOnEndTimestamp());
        $broadcast->setStopOnEndTimestamp(false);
        self::assertFalse($broadcast->isStopOnEndTimestamp());

        self::assertEquals(LiveBroadcast::PRIVACY_STATUS_PUBLIC, $broadcast->getPrivacyStatus());
        $broadcast->setPrivacyStatus(LiveBroadcast::PRIVACY_STATUS_PRIVATE);
        self::assertEquals(LiveBroadcast::PRIVACY_STATUS_PRIVATE, $broadcast->getPrivacyStatus());
    }
}
