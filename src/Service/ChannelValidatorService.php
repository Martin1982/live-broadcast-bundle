<?php declare(strict_types=1);

/**
 * live-broadcast-bundle - All rights reserved
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ChannelValidatorService
 */
class ChannelValidatorService
{
    /**
     * Interval between channel health checks in minutes
     */
    public const CHECK_INTERVAL = 15;

    /**
     * @var ObjectRepository
     */
    protected $channelRepository;

    /**
     * ChannelValidatorService constructor.
     *
     * @param KernelInterface        $kernel
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface     $validator
     */
    public function __construct(protected KernelInterface $kernel, protected EntityManagerInterface $entityManager, protected ValidatorInterface $validator)
    {
        $this->channelRepository = $entityManager->getRepository(AbstractChannel::class);
    }

    /**
     * Run a validation loop
     */
    public function validate(): void
    {
        $uniqFile = md5($this->kernel->getEnvironment());
        $tmpDir = sys_get_temp_dir();

        if (!is_writable($tmpDir)) {
            return;
        }

        $lastCheckedFile = $tmpDir.'/'.$uniqFile;
        touch($lastCheckedFile);
        $lastChecked = file_get_contents($lastCheckedFile);
        $checkTimestamp = 0;

        if (is_numeric($lastChecked)) {
            $checkTimestamp = $lastChecked;
        }

        $nowTimestamp = time();
        $diff = ($nowTimestamp / 60) - ($checkTimestamp / 60);

        if ($diff >= self::CHECK_INTERVAL) {
            $this->validateChannels();
            file_put_contents($lastCheckedFile, $nowTimestamp);
        }
    }

    /**
     * Validate current channels
     */
    protected function validateChannels(): void
    {
        /** @var AbstractChannel[] $channels */
        $channels = $this->channelRepository->findAll();

        foreach ($channels as $channel) {
            $errors = $this->validator->validate($channel);
            if (count($errors) > 0) {
                $channel->setIsHealthy(false);
            } else {
                $channel->setIsHealthy(true);
            }
            $this->entityManager->persist($channel);
        }

        $this->entityManager->flush();
    }
}
