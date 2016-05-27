<?php

namespace Martin1982\LiveBroadcastBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BroadcasterCommand
 * @package Martin1982\LiveBroadcastBundle\Command
 */
class BroadcasterCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('livebroadcaster:broadcast')
            ->setDescription('Run any broadcasts that haven\'t started yet and which are planned');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scheduler = $this->getContainer()->get('live.broadcast.scheduler');
        $scheduler->applySchedule();
    }
}