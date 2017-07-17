<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class LiveBroadcastTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity
 */
class LiveBroadcastTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test class instance.
     */
    public function testClass()
    {
        $broadcast = new LiveBroadcast();
        self::assertInstanceOf('Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast', $broadcast);
    }

    /**
     * Test get methods.
     */
    public function testGetMethods()
    {
        $now = new \DateTime();
        $endTime = new \DateTime('+1 hour');

        $broadcast = new LiveBroadcast();
        $broadcast->setName('Test');
        $broadcast->setDescription('Description of broadcast');
        $broadcast->setThumbnail(new File('test', false));

        self::assertEquals($now, $broadcast->getStartTimestamp());
        self::assertEquals($endTime, $broadcast->getEndTimestamp());
        self::assertEquals(new ArrayCollection(), $broadcast->getOutputChannels());
        self::assertEquals('Test', $broadcast->getName());
        self::assertEquals('Description of broadcast', $broadcast->getDescription());
        self::assertInstanceOf(File::class, $broadcast->getThumbnail());

        /* Check default value */
        self::assertTrue($broadcast->isStopOnEndTimestamp());
        $broadcast->setStopOnEndTimestamp(false);
        self::assertFalse($broadcast->isStopOnEndTimestamp());
    }
}
