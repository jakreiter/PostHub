<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * @Route("/kadmin/user")
 */
class UserAdminController extends AbstractController
{
    /**
     * @Route("/find.{_format}",
     *      requirements = { "_format" = "html|json" },
     *      name="user_admin_find", methods={"GET"})
     */
    public function findByFragmentAction(Request $request, $_format, UserRepository $userRepository): Response
    {
        $fragment = $request->query->get('q');
        if ($fragment) {
            $users = $userRepository->findByFragment($fragment);
        } else {
            $users = [];
        }

        if ('html' == $_format) {
            return $this->render('user_admin/index.html.twig', [
                'users' => $users,
            ]);
        } else {
            $orgArrs = [];
            if (count($users)) {
                foreach ($users as $user) {
                    $orgArrs[]=$user->toArray();
                }
            }
            $reaponseArr = [
                'results'=>$orgArrs
            ];
            $response = new JsonResponse();
            $response->setData($reaponseArr);
            return $response;
        }
    }

    /**
     * @Route("/", name="user_admin_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user_admin/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_admin_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $user->setEnabled(true);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_admin_index');
        }

        return $this->render('user_admin/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_admin_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user_admin/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_admin_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $translator->trans('Changes have been saved.'));
            return $this->redirectToRoute('user_admin_show', ['id'=>$user->getId()]);
        }

        return $this->render('user_admin/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_admin_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_admin_index');
    }
}
