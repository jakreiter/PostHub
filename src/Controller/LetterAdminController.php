<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Form\LetterHandoverSelectType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\LetterType;
use App\Form\Filter\LetterFilterType;
use App\Form\Filter\LetterHandoverFilterType;
use App\Entity\Letter;
use App\Repository\OrganizationRepository;
use App\Repository\LetterStatusRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\FileLetterService;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

/**
 * @Route("/kadmin/letter")
 */
class LetterAdminController extends AbstractController
{
    private $slugger;

    public function __construct(SluggerInterface $slugger, TransportInterface $mailer, TranslatorInterface $translator,
                                UrlGeneratorInterface $router)
    {
        $this->slugger = $slugger;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**
     * @Route("/print_handover", name="letter_admin_print_handover", methods={"GET", "POST"})
     */
    public function printHandover(PaginatorInterface $paginator, EntityManagerInterface $em, Request $request, OrganizationRepository $organizationRepository, LetterStatusRepository $letterStatusRepository): Response
    {
        $letters = [];
        $selectForm = $this->createForm(LetterHandoverSelectType::class);
        $selectForm->handleRequest($request);
        if ($selectForm->isSubmitted() && $selectForm->isValid()) {
            $letters = $selectForm->get('letters')->getData();
        }

        return $this->render('letter/print_handover.html.twig', [
            'letters' => $letters,
        ]);
    }

    /**
     * @Route("/handover", name="letter_admin_handover", methods={"GET","POST"})
     */
    public function lettersHandover(PaginatorInterface $paginator, EntityManagerInterface $em, Request $request, OrganizationRepository $organizationRepository, LetterStatusRepository $letterStatusRepository): Response
    {
        $filter = ['statuses' => [$letterStatusRepository->find(4), $letterStatusRepository->find(6)]];
        $filterForm = $this->createForm(LetterHandoverFilterType::class, $filter);

        $filterBuilder = $em->createQueryBuilder()
            ->select([
                'Letter', 'Organization', 'LetterStatus'
            ])
            ->from('App\Entity\Letter', 'Letter')
            ->leftJoin('Letter.organization', 'Organization')
            ->leftJoin('Letter.status', 'LetterStatus')
            ->indexBy('Letter', 'Letter.id')
        ;


        if ($request->query->has($filterForm->getName())) {

            $filter = $request->query->get($filterForm->getName());
            $filterForm->submit($filter);
            $organization = $organizationRepository->find($filter['organization']);
            $filterBuilder->andWhere('Letter.organization = :organization')->setParameter('organization', $organization);
            if (isset($filter['statuses']) && $filter['statuses']) {
                $filterBuilder->andWhere('LetterStatus.id IN (:statuses)')->setParameter('statuses', $filter['statuses']);
            }
            if (isset($filter['barcodes']) && $filter['barcodes'] && trim($filter['barcodes'])) {
                $barcodes = explode("\n", $filter['barcodes']);
                $barcodes = array_map('trim', $barcodes);
                if ($barcodes)
                    $filterBuilder->andWhere('Letter.barcodeNumber IN (:barcodeNumbers)')->setParameter('barcodeNumbers', $barcodes);
            }

            $query = $filterBuilder->getQuery();

            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                500 /*limit per page*/
            );

            $letters = $query->getResult();

            $selectForm = $this->createForm(LetterHandoverSelectType::class, [], ['letters'=>$letters]);
            $selectForm->handleRequest($request);
            if ($selectForm->isSubmitted() && $selectForm->isValid()) {

                $selectedLetters = $selectForm->get('letters')->getData();
                if ($selectForm->get('changeStatusBtn')->isClicked()) {
                    $givenStatus = $letterStatusRepository->find(5);
                    foreach ($selectedLetters as $letter) {
                        /** @var Letter $letter */
                        $letter->setStatus($givenStatus);
                    }
                    $em->flush();
                }

                return $this->render('letter/print_handover.html.twig', [
                    'letters' => $selectedLetters,
                    'todaysDate' => new \DateTime()
                ]);
            }

            return $this->render('letter/handover.html.twig', [
                'letters' => $letters,
                'filterForm' => $filterForm->createView(),
                'selectForm'=>$selectForm->createView(),
                'pagination' => $pagination,
            ]);

        } else {
            $letters = [];
            $pagination = null;
        }


        return $this->render('letter/handover.html.twig', [
            'letters' => $letters,
            'filterForm' => $filterForm->createView(),
            'pagination' => $pagination,
        ]);
    }


    /**
     * @Route("/", name="letter_admin_index", methods={"GET"})
     */
    public function index(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $filterForm = $this->createForm(LetterFilterType::class);

        $filterBuilder = $em->createQueryBuilder()
            ->select([
                'Letter', 'Organization', 'LetterStatus'
            ])
            ->from('App\Entity\Letter', 'Letter')
            ->leftJoin('Letter.organization', 'Organization')
            ->leftJoin('Letter.status', 'LetterStatus');


        if ($request->query->has($filterForm->getName())) {
            $filter = $request->query->get($filterForm->getName());


            if (isset($filter['title']) && $filter['title']) {
                $filterBuilder->andWhere('Letter.title LIKE :title')->setParameter('title', '%' . $filter['title'] . '%');
            }
            if (isset($filter['organization']) && $filter['organization']) {
                $filterBuilder->andWhere('Organization.id = :organization')->setParameter('organization', $filter['organization']);
            }
            if (isset($filter['status']) && $filter['status']) {
                $filterBuilder->andWhere('LetterStatus.id = :status')->setParameter('status', $filter['status']);
            }
            if (isset($filter['barcodeNumber']) && $filter['barcodeNumber']) {
                $filterBuilder->andWhere('Letter.barcodeNumber = :barcodeNumber')->setParameter('barcodeNumber', $filter['barcodeNumber']);
            }

            if (isset($filter['hasOrderedScanInserted']) && $filter['hasOrderedScanInserted']) {
                if (-1 == $filter['hasOrderedScanInserted']) {
                    $filterBuilder->andWhere('Letter.scanInserted IS NULL');
                    $filter['hasScanOrdered'] = 1;
                }
                if (1 == $filter['hasOrderedScanInserted']) $filterBuilder->andWhere('Letter.scanInserted IS NOT NULL');
            }
            if (isset($filter['hasScanOrdered']) && $filter['hasScanOrdered']) {
                if (-1==$filter['hasScanOrdered']) $filterBuilder->andWhere('Letter.scanOrdered IS NULL');
                if ( 1==$filter['hasScanOrdered']) $filterBuilder->andWhere('Letter.scanOrdered IS NOT NULL');
            }
            $filterForm->submit($filter);

        }

        $query = $filterBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $_ENV['PAGINATION_MAX_NUMBER_OF_ITEM_PER_PAGE'] /*limit per page*/
        );

        $letters = $query->getResult();

        return $this->render('letter/index.html.twig', [
            'letters' => $letters,
            'pagination' => $pagination,
            'form' => $filterForm->createView(),
        ]);
    }

    /**
     * @Route("/new", name="letter_new", methods="GET|POST")
     */

    public function newAction(Request $request): Response
    {

        $rich = $request->get('rich');
        $letter = new Letter();
        $letter->setCreatedByUser($this->getUser());
        $form = $this->createForm(LetterType::class, $letter);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile) {
                $this->handleFileUpload($uploadedFile, $letter);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($letter);
            $entityManager->flush();

            $dummyFormNumber = 'L' . $letter->getId();
            return $this->render('letter/saved.html.twig', [
                'letter' => $letter,
                'dummyFormNumber' => $dummyFormNumber
            ]);
        }
        if ($rich) {
            return $this->render('letter/new_rich.html.twig', [
                'letter' => $letter,
                'form' => $form->createView(),
            ]);
        } else {
            return $this->render('letter/new.html.twig', [
                'letter' => $letter,
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route("/show:{id}", name="letter_admin_show", methods={"GET"})
     */
    public function show(Letter $letter): Response
    {
        return $this->render('letter/show.html.twig', [
            'letter' => $letter,
        ]);
    }


    /**
     * @Route("/edit:{id}", name="letter_admin_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Letter $letter, FileLetterService $letterService): Response
    {
        $form = $this->createForm(LetterType::class, $letter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile) {
                $letterService->deleteFile($letter);
                $this->handleFileUpload($uploadedFile, $letter);
                if ($letter->getScanOrdered() && $letter->getFileName()) {
                    $letter->setScanInserted(new \DateTime());
                    $letter->setSeen(false);
                    // e-mail Notification
                    $organization = $letter->getOrganization();
                    if ($organization) {
                        $commaSeparatedEmails = null;
                        if ($organization->getCommaSeparatedEmails())
                            $commaSeparatedEmails = $organization->getCommaSeparatedEmails();
                        else if ($organization->getOwner())
                            $commaSeparatedEmails = $organization->getOwner()->getEmail();

                        if ($commaSeparatedEmails) {
                            $translator = $this->translator;
                            $mailer = $this->mailer;

                            $letters = [$letter];
                            if (count($letters)) {
                                $emails = explode(',', $commaSeparatedEmails);
                                $subject = $translator->trans('Completed scan notification');
                                $mailMessage = (new TemplatedEmail())
                                    ->subject($subject)
                                    ->htmlTemplate('emails/completed_scan_notification.html.twig')
                                    ->context(['organization' => $organization, 'letters' => $letters]);

                                foreach ($emails as $email) {
                                    $mailMessage->addTo($email);
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
                                    $this->getDoctrine()->getManager()->persist($notification);
                                    $notifications[] = $notification;

                                    foreach ($letters as $letter) {
                                        /** @var Letter $letter */
                                        if ($messageId) {
                                            $letter->setNotificationSent(true);
                                        }
                                        //$letter->setNotification($notification);
                                    }
                                    $this->getDoctrine()->getManager()->flush();
                                } catch (TransportExceptionInterface $e) {

                                }
                            }
                        }
                    }
                }
            }

            $letter->setModifiedByUser($this->getUser());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('letter_admin_index');
        }

        return $this->render('letter/edit.html.twig', [
            'letter' => $letter,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/multi", name="letter_multi", methods="GET")
     */
    public function multiFormAction(Request $request): Response
    {

        $letter = new Letter();

        $form0 = $this->createForm(LetterType::class, $letter);
        $form = $this->createForm(LetterType::class, $letter);


        return $this->render('letter/multi.html.twig', [
            'letter' => $letter,
            'form0' => $form0->createView(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete:{id}", name="letter_admin_delete", methods={"POST"})
     */
    public function delete(Request $request, Letter $letter): Response
    {
        if ($this->isCsrfTokenValid('delete' . $letter->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($letter);
            $entityManager->flush();
            $this->addFlash('danger', 'Deleted.');
        }

        return $this->redirectToRoute('letter_admin_index');
    }

    private function handleFileUpload($uploadedFile, Letter $letter): void
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $letter->setOriginalName($uploadedFile->getClientOriginalName());
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $this->slugger->slug($originalFilename);
        $orgDir = $letter->getOrganization()->getId();
        if (!is_dir($this->getParameter('upload_directory') . DIRECTORY_SEPARATOR . $orgDir)) {
            mkdir($this->getParameter('upload_directory') . DIRECTORY_SEPARATOR . $orgDir);
        }
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
        $filenameInOrgdir = $orgDir . DIRECTORY_SEPARATOR . $newFilename;

        // Move the file to the directory where files are stored
        try {
            $uploadedFile->move(
                $this->getParameter('upload_directory') . DIRECTORY_SEPARATOR . $orgDir,
                $newFilename
            );
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }
        $fileSize = filesize($this->getParameter('upload_directory') . DIRECTORY_SEPARATOR . $filenameInOrgdir);
        $letter->setSize($fileSize);
        $letter->setFilename($filenameInOrgdir);
    }

}