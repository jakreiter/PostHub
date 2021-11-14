<?php

namespace App\Controller;

use App\Form\Import1Type;
use App\Service\LetterImport1Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/kadmin/import")
 */
class ImportLettersAdminController extends AbstractController
{

    public function __construct(SluggerInterface $slugger, TransportInterface $mailer, TranslatorInterface $translator)
    {
        $this->slugger = $slugger;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }


    /**
     * @Route("/letters1", name="admin_letters_import1", methods={"GET","POST"})
     */
    public function import1(EntityManagerInterface $em, Request $request, LetterImport1Service $letterImportService): Response
    {
        $form = $this->createForm(Import1Type::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $selected = $form->getData();
            $resuInfo = $letterImportService->importLetters($selected['registered'], $selected['organization']);

            return $this->render('import/import1_results.html.twig', [
                'form' => $form->createView(),
                'resuInfo' => $resuInfo
            ]);
        }

        return $this->render('import/import1.html.twig', [
            'form' => $form->createView(),
        ]);


    }

    /**
     * @Route("/registered", name="admin_letters_import1_registered", methods={"GET","POST"})
     */
    public function getRegistered(EntityManagerInterface $em, Request $request): Response
    {
        $conn = $em->getConnection();
        $fragment = $request->query->get('fragment');
        if ($fragment) {
            $sql = '(SELECT id_registered AS id, name, company FROM registered
                WHERE CONVERT(registered.id_registered,char) LIKE ? ORDER BY company LIMIT 20)
                UNION
                (SELECT id_registered AS id, name, company FROM registered
                WHERE company LIKE ? ORDER BY company LIMIT 20)
                UNION
                (SELECT id_registered AS id, name, company FROM registered
                WHERE company LIKE ? ORDER BY company LIMIT 20)
                ';
            $rows = $conn->fetchAllAssociative($sql, [
                intval($fragment) . '%',
                $fragment . '%',
                '%' . $fragment . '%'
            ]);
        } else {
            $rows = [];
        }
        $response = new JsonResponse();
        $response->setData($rows);
        return $response;
    }


}