<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Command;

use Martin1982\LiveBroadcastBundle\Broadcaster\Scheduler;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BroadcasterCommand
 *
 * @codeCoverageIgnore
 */
#[AsCommand(name: 'livebroadcaster:broadcast', description: 'Run any broadcasts that haven\'t started yet and which are planned')]
class BroadcasterCommand extends Command
{
    /**
     * @param Scheduler       $scheduler
     * @param LoggerInterface $logger
     * @param int             $eventLoopTimer
     *
     */
    public function __construct(private Scheduler $scheduler, private LoggerInterface $logger, private int $eventLoopTimer = 10)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scheduler = $this->scheduler;

        $eventLoop = Loop::get();
        $eventLoop->addPeriodicTimer(
            $this->eventLoopTimer,
            function () use ($scheduler) {
                try {
                    $scheduler->applySchedule();
                } catch (\Throwable $exception) {
                    $this->logger->critical($exception->getMessage(), $exception->getTrace());
                }
            }
        );

        $eventLoop->run();

        return 0;
    }
}
