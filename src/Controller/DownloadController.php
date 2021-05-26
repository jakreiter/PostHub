<?php

namespace App\Controller;

use App\Entity\Letter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Service\FileLetterService;

/**
 * @Route("/download")
 * @Security("is_granted('ROLE_USER')")
 */
class DownloadController extends AbstractController
{

    private $fileLetterService;

    public function __construct(FileLetterService $fileLetterService)
    {
        $this->fileLetterService = $fileLetterService;
    }


    /**
     * @Route("/letter:{id}", name="letter_file_download", methods={"GET"})
     */
    public function download(Letter $letter): Response
    {

        if (
            ($this->getUser()->hasRole('ROLE_ADMIN'))
            || ($this->getUser()->hasRole('ROLE_LOCATION_MODERATOR') && $letter->getOrganization()->getLocation() == $this->getUser()->getLocation())
            || ($this->getUser()->getId() && $letter->getOrganization()->getOwner() == $this->getUser())
        ) {


            $file = $this->fileLetterService->getFilePath($letter);
            if (file_exists($file)) {
                $response = new BinaryFileResponse($file);
                $response->setContentDisposition(
                // ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    ResponseHeaderBag::DISPOSITION_INLINE,
                    $letter->getOriginalName()
                );
                return $response;
            }
            throw $this->createNotFoundException('The file has already been deleted.');
        } else {
            $response = $this->render('security/access_denied.html.twig');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }

    }



    /**
     * @Route("/kadmin/delfile:{id}", name="letter_file_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, Letter $letter): Response
    {
        if ($this->isCsrfTokenValid('delete' . $letter->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();
            $this->fileLetterService->deleteFile($letter);
            $letter->setFileName(null);
            $entityManager->remove($letter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('home');
    }
}
