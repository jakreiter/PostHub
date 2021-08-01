<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Model\ConsoleTools;
use App\Service\EmailNotificationService;

class NotificationSenderCommand extends Command
{
// the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:notification_sender';

    private $em;
    private $emailNotificationService;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag, EmailNotificationService $notificationService)
    {
        $this->em = $em;
        $this->emailNotificationService = $notificationService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('NotificationSenderCommand')
            ->setHelp('There is no options.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ConsoleTools::makeStyles($output);

        $output->writeln('NotificationSenderCommand');

        $this->emailNotificationService->setOutput($output);
        $this->emailNotificationService->sendNewLettersNotifications();

        return 0;
    }
}