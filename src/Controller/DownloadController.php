<?php

namespace App\Controller;

use App\Entity\Letter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\FileLetterService;

#[Route('/download')]
class DownloadController extends AbstractController
{

    private $fileLetterService;
    private EntityManagerInterface $em;

    public function __construct(FileLetterService $fileLetterService, EntityManagerInterface $em)
    {
        $this->fileLetterService = $fileLetterService;
        $this->em = $em;
    }

    #[Route('/letter_nl:{id}:{rapas}', name: 'letter_file_download_for_not_logged_in', methods: ['GET'])]
    public function downloadActionForNotLoggedInUser(Letter $letter, $rapas, Security $security): Response
    {

        if (
            $letter->getOrganization()->getAllowScanDownloadWithoutLogin()
        ) {
            if ($rapas==$letter->getRapas()) {
                return $this->downloadScan($letter, true);
            } else {
                return $this->redirectToRoute('letter_file_download', ['id'=>$letter->getId()]);
            }
        } else {
            return $this->redirectToRoute('letter_file_download', ['id'=>$letter->getId()]);
        }

    }


    #[Route('/letter:{id}', name: 'letter_file_download', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function downloadActionForLoggedInUser(Letter $letter, Security $security): Response
    {

        if (
            ($security->isGranted('ROLE_ADMIN'))
            || ($security->isGranted('ROLE_LOCATION_MODERATOR') && $letter->getOrganization()->getLocation() == $this->getUser()->getLocation())
            || ($this->getUser()->getId() && $letter->getOrganization()->getOwner() == $this->getUser())
        ) {
            return $this->downloadScan($letter, ($this->getUser()->getId() && $letter->getOrganization()->getOwner() == $this->getUser()));
        } else {
            $response = $this->render('security/access_denied.html.twig');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }

    }



    #[Route('/kadmin/delfile:{id}', name: 'letter_file_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Letter $letter): Response
    {
        if ($this->isCsrfTokenValid('delete' . $letter->getId(), $request->request->get('_token'))) {

            $this->fileLetterService->deleteFile($letter);
            $letter->setFileName(null);
            $this->em->remove($letter);
            $this->em->flush();
        }

        return $this->redirectToRoute('home');
    }

    private function downloadScan(Letter $letter, $seen=false): BinaryFileResponse
    {
        $file = $this->fileLetterService->getFilePath($letter);
        if (file_exists($file) && is_file($file)) {
            $response = new BinaryFileResponse($file);
            $response->setContentDisposition(
            // ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                ResponseHeaderBag::DISPOSITION_INLINE,
                $letter->getOriginalName()
            );

                if ($seen) $letter->setSeen(true);
                if ($this->getUser()) $letter->setDownloadedByUser($this->getUser());
                $this->em->flush();

            return $response;
        }
        throw $this->createNotFoundException('The file has already been deleted.');
    }
}
