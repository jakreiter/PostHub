<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\LetterType;
use App\Entity\Letter;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/kadmin/letter")
 */
class LetterAdminController extends AbstractController
{

    /**
     * @Route("/new", name="letter_new", methods="GET|POST")
     */

    public function newAction(Request $request, SluggerInterface $slugger): Response
    {

        $letter = new Letter();

        $form = $this->createForm(LetterType::class, $letter);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $uploadedFile = $form->get('file')->getData();
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $letter->setOriginalName($uploadedFile->getClientOriginalName());
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $uploadedFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $fileSize = filesize($this->getParameter('upload_directory').DIRECTORY_SEPARATOR.$newFilename);
                $letter->setSize($fileSize);
                $letter->setFilename($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($letter);
            $entityManager->flush();

        }
        return $this->render('letter/new.html.twig', [
            'letter' => $letter,
            'form' => $form->createView()
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

}