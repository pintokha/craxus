<?php

namespace Pintokha\Craxus\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected static $defaultName = 'status';

    protected function configure()
    {
        $this
            // the short description shown while running "php craxus list"
            ->setDescription('Check your connection')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command lets you determine if you have successfully connected to Craxus')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Hi, Craxus successfully configured</info>');
        $output->writeln('<comment>Current project:   PMFarma</comment>');
        $output->writeln('<comment>Your username:     pintokha</comment>');

        return 0;
    }
}