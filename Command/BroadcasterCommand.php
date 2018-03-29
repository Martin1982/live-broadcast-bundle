<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Command;

use Martin1982\LiveBroadcastBundle\Broadcaster\Scheduler;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BroadcasterCommand
 *
 * @codeCoverageIgnore
 */
class BroadcasterCommand extends Command
{
    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $eventLoopTimer;

    /**
     * @var string
     */
    protected static $defaultName = 'livebroadcaster:broadcast';

    /**
     * @param Scheduler       $scheduler
     * @param LoggerInterface $logger
     * @param int             $eventLoopTimer
     *
     */
    public function __construct(Scheduler $scheduler, LoggerInterface $logger, $eventLoopTimer = 10)
    {
        $this->scheduler = $scheduler;
        $this->logger = $logger;
        $this->eventLoopTimer = $eventLoopTimer;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setDescription('Run any broadcasts that haven\'t started yet and which are planned');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scheduler = $this->scheduler;

        $eventLoop = Factory::create();
        $eventLoop->addPeriodicTimer(
            $this->eventLoopTimer,
            function () use ($scheduler, $output) {
                try {
                    $scheduler->applySchedule();
                } catch (\Throwable $exception) {
                    $this->logger->critical($exception->getMessage());
                }
            }
        );

        $eventLoop->run();
    }
}
