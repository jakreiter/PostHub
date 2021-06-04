<?php

namespace App\Controller;

use App\Entity\PassSetter;
use App\Form\UserPassSetType;
use App\Model\PassHelperTools;
use App\Model\StringTools;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\User;
use App\Form\UserType;
use App\Form\Filter\UserFilterType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


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
                    $orgArrs[] = $user->toArray();
                }
            }
            $reaponseArr = [
                'results' => $orgArrs
            ];
            $response = new JsonResponse();
            $response->setData($reaponseArr);
            return $response;
        }
    }

    /**
     * @Route("/", name="user_admin_index", methods={"GET"})
     */
    public function index(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $filterForm = $this->createForm(UserFilterType::class);

        $filterBuilder = $em->createQueryBuilder()
            ->select([
                'User'
            ])
            ->from('App\Entity\User', 'User');


        if ($request->query->has($filterForm->getName())) {
            $filter = $request->query->get($filterForm->getName());
            $filterForm->submit($filter);

            if (isset($filter['username']) && $filter['username']) {
                $filterBuilder->andWhere('User.username LIKE :username')->setParameter('username', '%' . $filter['username'] . '%');
            }
            if (isset($filter['firstName']) && $filter['firstName']) {
                $filterBuilder->andWhere('User.firstName LIKE :firstName')->setParameter('firstName', '%' . $filter['firstName'] . '%');
            }
            if (isset($filter['lastName']) && $filter['lastName']) {
                $filterBuilder->andWhere('User.lastName LIKE :lastName')->setParameter('lastName', '%' . $filter['lastName'] . '%');
            }
            if (isset($filter['email']) && $filter['email']) {
                $filterBuilder->andWhere('User.email LIKE :email')->setParameter('email', $filter['email']);
            }

        }

        $query = $filterBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $_ENV['PAGINATION_MAX_NUMBER_OF_ITEM_PER_PAGE'] /*limit per page*/
        );

        $users = $query->getResult();

        return $this->render('user_admin/index.html.twig', [
            'users' => $users,
            'pagination' => $pagination,
            'form' => $filterForm->createView(),
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
     * @Route("/{id}/change_password", name="user_admin_change_password", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function changePassword(Request $request, User $user, UserPasswordEncoderInterface $encoder,
                                   TranslatorInterface $translator): Response
    {

        for ($i = 0; $i < 10; $i++) {
            $randomPasswords[] = PassHelperTools::randomString();
        }
        $randomPasswords[] = StringTools::randomString() . StringTools::randomString() . StringTools::randomString();

        if ($user) {

            $passSetter = new PassSetter();
            $form = $this->createForm(UserPassSetType::class, $passSetter);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($passSetter->getNewPassword() == $passSetter->getNewPassword2()) {

                    $plainPassword = $passSetter->getNewPassword();
                    $encoded = $encoder->encodePassword($user, $plainPassword);
                    $user->setPassword($encoded);
                    $user->setPassResetToken(null);
                    $user->setLastPassResetRequest(null);
                    $this->getDoctrine()
                        ->getManager()
                        ->flush();


                    $this->addFlash('success', $translator->trans('Password changed.'));
                    return $this->redirectToRoute('user_admin_edit', ['id' => $user->getId()]);
                } else {
                    $this->addFlash('danger', $translator->trans('New passwords not match.'));
                }
                return $this->redirectToRoute('user_admin_change_password', ['id' => $user->getId()]);
            }

            return $this->render('user_admin/set_pass.html.twig', [
                'passChanger' => $passSetter,
                'form' => $form->createView(),
                'randomPasswords' => $randomPasswords,
                'user'=>$user
            ]);
        } else {
            return $this->render('error/common.html.twig', [
                'error' => [
                    'title' => $translator->trans('Error'),
                    'description' => $translator->trans('Wrong or expired link.')
                ]
            ]);
        }

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
            return $this->redirectToRoute('user_admin_show', ['id' => $user->getId()]);
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
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_admin_index');
    }
}
