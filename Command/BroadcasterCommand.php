<?php

namespace Martin1982\LiveBroadcastBundle\Command;

use Martin1982\LiveBroadcastBundle\Broadcaster\Scheduler;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BroadcasterCommand
 * @package Martin1982\LiveBroadcastBundle\Command
 */
class BroadcasterCommand extends Command
{
    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var bool
     */
    private $eventLoopEnabled;

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
     * @param bool      $eventLoopEnabled
     * @param int       $eventLoopTimer
     */
    public function __construct(Scheduler $scheduler, $eventLoopEnabled = false, $eventLoopTimer = 10)
    {
        $this->scheduler = $scheduler;
        $this->eventLoopEnabled = $eventLoopEnabled;
        $this->eventLoopTimer = $eventLoopTimer;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
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

        if (!$this->eventLoopEnabled) {
            $scheduler->applySchedule();

            return;
        }

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
