<?php

namespace App\Service;

use App\Entity\Letter;
use App\Entity\Notification;
use App\Repository\LetterRepository;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class EmailNotificationService
{
    private $em;
    private $doctrine;
    private $environment;
    private $mailer;
    private $translator;
    private $output;

    private $letterRepository;
    private $organizationRepository;
    private $projectDir;

    public function __construct(EntityManagerInterface $em, $environment, ManagerRegistry $doctrine, ParameterBagInterface $parameterBag,
                                TransportInterface $mailer, TranslatorInterface $translator,
                                UrlGeneratorInterface $router,
                                LetterRepository $letterRepository, OrganizationRepository $organizationRepository, $projectDir)
    {
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->letterRepository = $letterRepository;
        $this->organizationRepository = $organizationRepository;

        $this->em = $em;
        $this->environment = $environment;
        $this->projectDir = $projectDir;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function getNumberOfRequiringNewLetterNotificationPerOrganization()
    {
        $em = $this->em;


        $query = $em->createQuery('SELECT organization.id,
                                            COUNT(letter.id) AS letters,                                           
                                            MAX(letter.lastAttemptToSendNotification) AS lastAttemptToSendNotification                                           
                                            FROM App\Entity\Letter letter
                                            LEFT JOIN letter.organization organization
                                            INDEX BY organization.id 
                                            WHERE    
                                             letter.notificationSent = false
                                            GROUP BY organization.id
                                            HAVING letters>0');
        return $query->getResult();
    }

    public function sendNewLettersNotifications($maxNumberOfOrganizations = 0): array
    {
        $notifications = [];
        $requiringNotificationInfoPerOrganization = $this->getNumberOfRequiringNewLetterNotificationPerOrganization();

        $mailer = $this->mailer;
        $translator = $this->translator;

        $oldEnough = new \DateTime($_ENV['TRY_TO_RESEND_NOTIFICATIONS_AFTER']);

        $organizationNumber = 0;
        foreach ($requiringNotificationInfoPerOrganization as $orgId => $infoPerOrganization) {

            if ($this->output) $this->output->write("orgId: $orgId\t number of letters:<value>{$infoPerOrganization['letters']}</value> lastAttemptToSendNotification:<code>{$infoPerOrganization['lastAttemptToSendNotification']}</code>");
            if (!$infoPerOrganization['lastAttemptToSendNotification'] || new \DateTime($infoPerOrganization['lastAttemptToSendNotification']) < $oldEnough) {
                if ($this->output) $this->output->writeln(" - <code>sending Notification</code>");
                $organization = $this->organizationRepository->find($orgId);
                if ($organization) {

                    $commaSeparatedEmails = '';
                    if ($organization->getCommaSeparatedEmails()) {
                        $commaSeparatedEmails.= $organization->getCommaSeparatedEmails();
                    }
                    if ($organization->getOwner()) {
                        if ($commaSeparatedEmails) $commaSeparatedEmails.=',';
                        $commaSeparatedEmails.= $organization->getOwner()->getEmail();
                    }
                    else {
                        $this->output->writeln("Owner: [".$organization->getOwner()."] <error>No e-mail</error>");
                        continue;
                    }
                    if ($commaSeparatedEmails) {

                        if ($this->output) {
                            $this->output->write("\t" . $organization->getName() . "\t ");
                            $this->output->write($commaSeparatedEmails . "\t ");
                        }
                        $letters = $this->letterRepository->findRequiringNotificationForOrganization($organization);
                        foreach ($letters as $letter) {
                            /** @var Letter $letter */
                            $letter->setLastAttemptToSendNotification(new \DateTime());
                        }
                        $this->em->flush();
                        if (count($letters)) {
                            $emails = explode(',', $commaSeparatedEmails);
                            $subject = $translator->trans('New letters notification');
                            $emailTemplate = $this->getRightEmailTemplate('new_letters_notification.html.twig');
                            if ($this->output) {
                                $this->output->write(" Template: ".$emailTemplate . "\t ");
                            }
                            $mailMessage = (new TemplatedEmail())
                                ->subject($subject)
                                ->htmlTemplate($emailTemplate)
                                ->context(['organization' => $organization, 'letters' => $letters]);

                            foreach ($emails as $email) {
                                $mailMessage->addTo($email);
                            }
                            if ($_ENV['NOTIFICATION_BCC_EMAIL']) {
                                $mailMessage->addBcc($_ENV['NOTIFICATION_BCC_EMAIL']);
                            }
                            try {
                                $entMessageInfo = $mailer->send($mailMessage);
                                $messageId = $entMessageInfo->getMessageId();
                                $notification = new Notification();
                                $notification->setOrganization($organization);
                                $notification->setSentMessageId($messageId);
                                $notification->setTitle($subject);
                                $notification->setContents($entMessageInfo->getMessage()->toString());
                                $notification->setDebug($entMessageInfo->getDebug());
                                $notification->setRecipient($commaSeparatedEmails);
                                $this->em->persist($notification);
                                $notifications[] = $notification;

                                foreach ($letters as $letter) {
                                    /** @var Letter $letter */
                                    if ($messageId) {
                                        $letter->setNotificationSent(true);
                                        if ($this->output) $this->output->write(" lt: <value>{$letter->getId()}</value> ");
                                    }
                                    $letter->setNotification($notification);
                                }
                                $this->em->flush();
                                if ($this->output) $this->output->write(" notification: <value>" . $notification->getId() . "</value> ");
                            } catch (TransportExceptionInterface $e) {
                                if ($this->output) {
                                    $this->output->writeln(" Error while sending.");
                                    $this->output->writeln($e->getMessage());
                                }
                            }
                        }
                    }
                }
            } else {
                if ($this->output) $this->output->writeln(" - not sending Notification");
            }

            $organizationNumber++;
            if ($maxNumberOfOrganizations && $organizationNumber>=$maxNumberOfOrganizations) break;
        }

        if ($this->output) $this->output->writeln("");
        return $notifications;
    }

    public function getRightEmailTemplate($templateFilenameAlone): string
    {
        $defaultTemplateFilename = 'emails'.DIRECTORY_SEPARATOR.$templateFilenameAlone;
        $customizedTemplateFilename = 'emails_customized'.DIRECTORY_SEPARATOR.$templateFilenameAlone;
        $templateFilenameToUse = file_exists($this->projectDir.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$customizedTemplateFilename) ? $customizedTemplateFilename : $defaultTemplateFilename;
        return $templateFilenameToUse;
    }
}