<?php

namespace Martin1982\LiveBroadcastBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShellTestCommand.
 */
class ShellTestCommand extends ContainerAwareCommand
{
    /** @var bool $isWindows */
    private $isWindows = false;

    /** @var bool $isMac */
    private $isMac = false;

    /** @var bool $isLinux */
    private $isLinux = false;

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
        switch (strtoupper(substr(PHP_OS, 0, 3))) {
            case 'WIN':
                $this->isWindows = true;
                break;
            case 'DAR':
                $this->isMac = true;
                break;
            default:
                $this->isLinux = true;
                break;
        }

        $output->write('Checking \'ffmpeg\' command availability... ');
        $this->testFfmpeg($output);

        if ($this->isWindows) {
            $output->write('Checking \'tasklist\' command availability... ');
            $this->testTasklist($output);

            $output->write('Checking \'taskkill\' command availability... ');
            $this->testTaskkill($output);
        }

        if ($this->isMac || $this->isLinux) {
            $output->write('Checking \'ps\' command availability... ');
            $this->testPs($output);

            $output->write('Checking \'kill\' command availability... ');
            $this->testKill($output);
        }

        if ($this->isMac) {
            $output->write('Checking \'grep\' command availability... ');
            $this->testGrep($output);
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
        exec('echo "got grep" | grep "got grep"', $cmdResult);

        return $this->analyseResult($cmdResult, 'got grep', $output);
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
