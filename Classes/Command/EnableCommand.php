<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\Crowdin\Command;

use FriendsOfTYPO3\Crowdin\Setup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EnableCommand extends Command
{
    public function __construct(
        private readonly Setup $setup,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setup->enable();
        $io = new SymfonyStyle($input, $output);
        $io->success('Crowdin enabled');

        return Command::SUCCESS;
    }
}
