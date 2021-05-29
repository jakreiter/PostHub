<?php

namespace App\Controller;

use App\Repository\LetterRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
     * @Route("/", name="letter_admin_index", methods={"GET"})
     */
    public function index(LetterRepository $letterRepository): Response
    {
        return $this->render('letter/index.html.twig', [
            'letters' => $letterRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="letter_new", methods="GET|POST")
     */

    public function newAction(Request $request, SluggerInterface $slugger): Response
    {

        $rich = $request->get('rich');
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
                $orgDir = $letter->getOrganization()->getId();
                if (!is_dir($orgDir)) {
                    mkdir($this->getParameter('upload_directory') . DIRECTORY_SEPARATOR . $orgDir);
                }
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
                $filenameInOrgdir = $orgDir . DIRECTORY_SEPARATOR . $newFilename;

                // Move the file to the directory where brochures are stored
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

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($letter);
            $entityManager->flush();

            $dummyFormNumber = 'L'.$letter->getId();
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
    public function edit(Request $request, Letter $letter): Response
    {
        $form = $this->createForm(LetterType::class, $letter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

}