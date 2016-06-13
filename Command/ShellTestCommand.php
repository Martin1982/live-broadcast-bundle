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
    /** @var bool $isWindows */
    private $isWindows = false;

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
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->isWindows = true;
        } else {
            $this->isWindows = false;
        }

        $output->write('Checking \'ffmpeg\' command availability... ');
        $this->testFfmpeg($output);

        if ($this->isWindows) {
            $output->write('Checking \'tasklist\' command availability... ');
            $this->testTasklist($output);

            $output->write('Checking \'taskkill\' command availability... ');
            $this->testTaskkill($output);
        } else {
            $output->write('Checking \'ps\' command availability... ');
            $this->testPs($output);

            $output->write('Checking \'kill\' command availability... ');
            $this->testKill($output);
        }
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
     * Test tasklist availability.
     *
     * @param OutputInterface $output
     */
    protected function testTasklist(OutputInterface $output)
    {
        exec('tasklist /?', $cmdResult);

        return $this->analyseResult($cmdResult, 'currently running processes', $output);
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
     * Test taskkill availability.
     *
     * @param OutputInterface $output
     */
    protected function testTaskkill(OutputInterface $output)
    {
        exec('taskkill /?', $cmdResult);

        return $this->analyseResult($cmdResult, 'terminate tasks by process id', $output);
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
