<?php

namespace Checker\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckMembershipTypeCommand extends AbstractCheckerCommand
{
    protected static $defaultName = 'check:membership:type';
    protected static $defaultDescription = 'Check and update membership types when necessary.';

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this->getCheckerService()->checkProperMembershipType();

        return Command::SUCCESS;
    }
}
