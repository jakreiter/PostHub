<?php

namespace App\Service;

use App\Entity\Letter;

use App\Entity\Organization;
use App\Repository\LetterRepository;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Repository\LetterStatusRepository;

class LetterImport1Service
{
    private $output;
    private $em;
    private $doctrine;
    private $environment;
    private $letterRepository;
    private $organizationRepository;
    private $letterStatusRepository;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function __construct(EntityManagerInterface $em, $environment, ManagerRegistry $doctrine, ParameterBagInterface $parameterBag,
                                UrlGeneratorInterface $router,
                                LetterRepository $letterRepository,
                                OrganizationRepository $organizationRepository,
                                LetterStatusRepository $letterStatusRepository)
    {

        $this->doctrine = $doctrine;
        $this->letterRepository = $letterRepository;
        $this->letterStatusRepository = $letterStatusRepository;
        $this->organizationRepository = $organizationRepository;

        $this->em = $em;
        $this->environment = $environment;
    }


    public function importLetters(int $registered_id, Organization $organization)
    {
        $statusByOryginalId[100] = $this->letterStatusRepository->find(5); // w biurze
        $statusByOryginalId[101] = $this->letterStatusRepository->find(6); // przekazana
        $statusByOryginalId[102] = $this->letterStatusRepository->find(7); // w biurze/skan
        $statusByOryginalId[109] = $this->letterStatusRepository->find(8); // wysłana pocztą

        $em = $this->em;
        $sql = 'SELECT id_letter, status_dc, scan_dc, id_registered, id_resu, login, notified_by_email, title, company, passkey,
             description, scanfile, org_name, file_type, size, date_added, modified, registered_skan_yn, scan_ordered, scan_inserted, scan_due
             FROM letter
             WHERE id_registered = ?';
        $conn = $em->getConnection();
        $rows = $conn->fetchAllAssociative($sql, [
            $registered_id
        ]);
        $numberOfImportedLetters = 0;
        $numberOfNotImportedLetters = 0;
        $importResults = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $existingLetter = $this->letterRepository->findOneBy(['foreignId'=>$row['id_letter']]);
                if ($existingLetter) {
                    $importResults[$row['id_letter']]['title'] = $row['title'];
                    $importResults[$row['id_letter']]['date_added'] = $row['date_added'];
                    $importResults[$row['id_letter']]['letter'] = $existingLetter;
                    $importResults[$row['id_letter']]['reason'] = 'Has already been imported';
                    $numberOfNotImportedLetters++;
                } else {

                    $letterStatus = $statusByOryginalId[$row['status_dc']];
                    $letter = new Letter();
                    $letter->setForeignId($row['id_letter']);
                    $letter->setOrganization($organization);
                    $letter->setCreated(new \DateTime($row['date_added']));
                    $letter->setUpdated(new \DateTime($row['modified']));
                    $letter->setNotificationSent($row['notified_by_email']);
                    $letter->setTitle($row['title']);
                    $letter->setStatus($letterStatus);
                    $importedLetters[] = $letter;
                    $em->persist($letter);

                    $importResults[$row['id_letter']]['title'] = $row['title'];
                    $importResults[$row['id_letter']]['date_added'] = $row['date_added'];
                    $importResults[$row['id_letter']]['letter'] = $letter;
                    $importResults[$row['id_letter']]['reason'] = null;
                    $numberOfImportedLetters++;
                }
            }
            $em->flush();
        }
        return [
            'numberOfImported' => $numberOfImportedLetters,
            'numberOfNotImported' => $numberOfNotImportedLetters,
            'importResults' => $importResults
        ];
    }

}