<?php

namespace App\Service;

use App\Entity\Letter;

use App\Repository\LetterRepository;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FileLetterService
{
    private $uploadPernDirectory;
    private $output;
    private $em;
    private $doctrine;
    private $environment;
    private $mailer;
    private $translator;

    private $letterRepository;
    private $organizationRepository;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function __construct(EntityManagerInterface $em, $environment, ManagerRegistry $doctrine, ParameterBagInterface $parameterBag,
                                TransportInterface $mailer, TranslatorInterface $translator,
                                UrlGeneratorInterface $router,
                                LetterRepository $letterRepository, OrganizationRepository $organizationRepository)
    {
        $this->uploadPernDirectory = $parameterBag->get('upload_directory');

        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->letterRepository = $letterRepository;
        $this->organizationRepository = $organizationRepository;

        $this->em = $em;
        $this->environment = $environment;
    }


    /**
     * Path to stored file (on server)
     * @param Letter $letter
     * @return string
     */
    public function getFilePath(Letter $letter): string
    {

        $filePath = $this->uploadPernDirectory . DIRECTORY_SEPARATOR . $letter->getFileName();

        return $filePath;
    }


    public function deleteFile(Letter $letter): bool
    {
        $filePath = $this->getFilePath($letter);
        if ($letter->getFileName() && file_exists($filePath)) {
            $unlinkResult = unlink($filePath);
            if ($this->output) $this->output->writeln("<value>" . $filePath . "</value>\t file deleted");
            $letter->setFileName(null);
            $this->em->flush();
            return $unlinkResult;
        } else {
            if ($this->output) $this->output->writeln(" <error>No file</error>");
            $letter->setFileName(null);
            $this->em->flush();
        }

        return false;
    }

    public function getOldLettersWithScans()
    {
        $aLongTimeAgoDate = new \DateTime('-' . $_ENV['NUMBER_OF_DAYS_AFTER_WHICH_THE_SCANS_SHOULD_BE_DELETED'] . ' days');
        $em = $this->em;
        $filterBuilder = $em->createQueryBuilder()
            ->select([
                'Letter', 'Organization', 'LetterStatus'
            ])
            ->from('App\Entity\Letter', 'Letter')
            ->leftJoin('Letter.organization', 'Organization')
            ->leftJoin('Letter.status', 'LetterStatus');
        $filterBuilder->andWhere('Letter.updated < :dtt')->setParameter('dtt', $aLongTimeAgoDate);
        $filterBuilder->andWhere('Letter.fileName IS NOT NULL');
        $query = $filterBuilder->getQuery();
        return $query->getResult();
    }

    public function deleteOldScans()
    {
        $ltrs = $this->getOldLettersWithScans();
        $numberOfLetters = count($ltrs);
        if ($this->output) $this->output->writeln("numberOfLetters: <value>$numberOfLetters</value>");
        foreach ($ltrs as $letter) {
            /**
             * @var Letter $letter
             */
            if ($this->output) {
                $this->output->write($letter->getId());
                $this->output->write("\t");
                $this->output->write($letter->getOrganization()->getId());
                $this->output->write("\t");
                $this->output->write('<value>' . $letter->getFileName() . '</value>');

            }
            if ($orgNumberOfDaysAfterWhichTheScansShouldBeDeleted = $letter->getOrganization()->getNumberOfDaysAfterWhichTheScansShouldBeDeleted()) {
                $orgLongTimeAgoDate = new \DateTime('-' . $orgNumberOfDaysAfterWhichTheScansShouldBeDeleted . ' days');
                if ($letter->getUpdated() >= $orgLongTimeAgoDate ) {
                    if ($this->output) {
                        $this->output->write(" org getNumberOfDaysAfterWhichTheScansShouldBeDeleted:");
                        $this->output->write($orgNumberOfDaysAfterWhichTheScansShouldBeDeleted." ");
                        if ($this->output) $this->output->writeln('');
                    }
                    continue;
                }
            }
            $result = $this->deleteFile($letter);
            if ($this->output) {
                $this->output->write("\t");
                $this->output->write($result);
            }
            if ($this->output) $this->output->writeln('');
        }
    }

}