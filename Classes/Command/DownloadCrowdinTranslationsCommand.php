<?php
declare(strict_types=1);

namespace GeorgRinger\Crowdin\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use GeorgRinger\Crowdin\Service\DownloadCrowdinTranslationService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadCrowdinTranslationsCommand extends BaseCommand
{

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Download CORE translations')
            ->addArgument('language', InputArgument::REQUIRED, 'Language')
            ->addArgument('branch', InputArgument::OPTIONAL, 'Branch', 'master')
            ->addArgument('copyToL10n', InputArgument::OPTIONAL, 'If set, the downloads are copied to l10n dir as well', false);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->showProjectIdentifier($io);

        $service = new DownloadCrowdinTranslationService();

        $service->downloadPackage(
            $input->getArgument('language'),
            $input->getArgument('branch'),
            (bool)$input->getArgument('copyToL10n')
        );

        $io->success('Data has been downloaded');
    }
}
