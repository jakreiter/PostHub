<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Model\ConsoleTools;
use App\Service\FileLetterService;

class OldFileDeleteCommand extends Command
{
// the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:old_file_delete';

    private $em;
    private $fileLetterService;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag, FileLetterService $fileLetterService)
    {
        $this->em = $em;
        $this->fileLetterService = $fileLetterService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('OldFileDelete')
            ->setHelp('There is no options.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ConsoleTools::makeStyles($output);

        $output->writeln('NotificationSenderCommand');

        $this->fileLetterService->setOutput($output);
        $this->fileLetterService->deleteOldScans();
        return 0;
    }
}