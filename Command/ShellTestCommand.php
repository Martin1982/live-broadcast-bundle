<?php

namespace Martin1982\LiveBroadcastBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShellTestCommand
 * @package Martin1982\LiveBroadcastBundle\Command
 */
class ShellTestCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('livebroadcaster:test:shell')
            ->setDescription('Test if the environment supports the right commands');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Checking \'ffmpeg\' command availability... ');
        $this->testFfmpeg($output);

        $output->write('Checking \'ps\' command availability... ');
        $this->testPs($output);

        $output->write('Checking \'kill\' command availability... ');
        $this->testKill($output);
    }

    /**
     * Test FFMpeg availability.
     *
     * @param OutputInterface $output
     */
    protected function testFfmpeg(OutputInterface $output)
    {
        exec('ffmpeg -version', $cmdResult);
        $this->analyseResult($cmdResult, 'ffmpeg version', $output);
    }

    /**
     * Test ps availability.
     *
     * @param OutputInterface $output
     */
    protected function testPs(OutputInterface $output)
    {
        exec('/bin/ps -o pid', $cmdResult);

        return $this->analyseResult($cmdResult, 'PID', $output);
    }

    /**
     * Test grep availability.
     *
     * @param OutputInterface $output
     */
    protected function testGrep(OutputInterface $output)
    {
        exec('grep --help', $cmdResult);

        return $this->analyseResult($cmdResult, 'Usage:', $output);
    }

    /**
     * Test kill availability.
     *
     * @param OutputInterface $output
     */
    protected function testKill(OutputInterface $output)
    {
        exec('kill -l', $cmdResult);

        return $this->analyseResult($cmdResult, 'QUIT', $output);
    }

    /**
     * Test ps availability.
     *
     * @param array           $cmdResult
     * @param string          $testable
     * @param OutputInterface $output
     */
    protected function analyseResult($cmdResult, $testable, OutputInterface $output)
    {
        if (count($cmdResult) && false !== strpos(implode($cmdResult), $testable)) {
            $output->writeln('<info>[OK]</info>');

            return;
        }

        $output->writeln('<error>[FAIL]</error>');
    }
}
