<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShellTestCommand
 */
#[AsCommand(name: 'livebroadcaster:test:shell', description: 'Test if the environment supports the right commands')]
class ShellTestCommand extends Command
{
    /**
     * @var bool
     */
    private bool $isWindows = false;

    /**
     * @var bool
     */
    private bool $isMac = false;

    /**
     * @var bool
     */
    private bool $isLinux = false;

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
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

        $output->writeln(sprintf('Detected OS is \'%s\'', PHP_OS));
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

        return 0;
    }

    /**
     * Test FFMpeg availability.
     *
     * @param OutputInterface $output
     */
    protected function testFfmpeg(OutputInterface $output): void
    {
        exec('ffmpeg -version', $cmdResult);
        $this->analyseResult($cmdResult, 'ffmpeg version (3|4).', $output);
    }

    /**
     * Test ps availability.
     *
     * @param OutputInterface $output
     */
    protected function testPs(OutputInterface $output): void
    {
        exec('/bin/ps -o pid', $cmdResult);
        $this->analyseResult($cmdResult, 'PID', $output);
    }

    /**
     * Test tasklist availability.
     *
     * @param OutputInterface $output
     */
    protected function testTasklist(OutputInterface $output): void
    {
        exec('tasklist /?', $cmdResult);
        $this->analyseResult($cmdResult, 'currently running processes', $output);
    }

    /**
     * Test grep availability.
     *
     * @param OutputInterface $output
     */
    protected function testGrep(OutputInterface $output): void
    {
        exec('echo "got grep" | grep "got grep"', $cmdResult);
        $this->analyseResult($cmdResult, 'got grep', $output);
    }

    /**
     * Test taskkill availability.
     *
     * @param OutputInterface $output
     */
    protected function testTaskkill(OutputInterface $output): void
    {
        exec('taskkill /?', $cmdResult);
        $this->analyseResult($cmdResult, 'terminate tasks by process id', $output);
    }

    /**
     * Test kill availability.
     *
     * @param OutputInterface $output
     */
    protected function testKill(OutputInterface $output): void
    {
        exec('kill -l', $cmdResult);
        $this->analyseResult($cmdResult, 'QUIT', $output);
    }

    /**
     * Test ps availability.
     *
     * @param array           $cmdResult
     * @param string          $testable
     * @param OutputInterface $output
     */
    protected function analyseResult(array $cmdResult, string $testable, OutputInterface $output): void
    {
        if (count($cmdResult) && 0 !== preg_match('/'.$testable.'/', implode($cmdResult))) {
            $output->writeln('<info>[OK]</info>');

            return;
        }

        $output->writeln('<error>[FAIL]</error>');
    }
}
