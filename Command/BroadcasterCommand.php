<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Command;

use Martin1982\LiveBroadcastBundle\Broadcaster\Scheduler;
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
     * @var int
     */
    private $eventLoopTimer;

    /**
     * @var string
     */
    protected static $defaultName = 'livebroadcaster:broadcast';

    /**
     * @param Scheduler $scheduler
     * @param int       $eventLoopTimer
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(Scheduler $scheduler, $eventLoopTimer = 10)
    {
        $this->scheduler = $scheduler;
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
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     * @throws \Doctrine\ORM\Query\QueryException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scheduler = $this->scheduler;

        $eventLoop = Factory::create();
        $eventLoop->addPeriodicTimer(
            $this->eventLoopTimer,
            function () use ($scheduler) {
                $scheduler->applySchedule();
            }
        );

        $eventLoop->run();
    }
}
