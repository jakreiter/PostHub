<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MeUserEmailType;
use App\Form\MeUserPassType;
use App\Form\MeUserEmailConfirmationType;
use App\Model\StringTools;
use App\Repository\UserRepository;
use App\Service\EmailNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Model\PassHelperTools;
use Symfony\Component\Mime\Email;

#[Route('/me/user')]
class UserUserController extends AbstractController
{

    private $emailNotificationService;
    private EntityManagerInterface $em;

    public function __construct(EmailNotificationService $emailNotificationService, EntityManagerInterface $em)
    {
        $this->emailNotificationService = $emailNotificationService;
        $this->em = $em;
    }

    #[Route('/', name: 'self_user_show', methods: 'GET')]
    public function show(): Response
    {
        $user = $this->getUser();
        return $this->render('me_user/show.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/change_pass', name: 'self_user_change_pass', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $hasher, TranslatorInterface $translator): Response
    {

        $formData = [];
        $form = $this->createForm(MeUserPassType::class, $formData, ['block_prefix' => 'a']);
        $form->handleRequest($request);

        $user = $this->getUser();

        for ($i = 0; $i < 10; $i++) {
            $randomPasswords[] = PassHelperTools::randomString();
        }

        if ($form->isSubmitted() && $form->isValid()) {


            $plainOldPassword = $form->get('password')->getData();

            if ($hasher->isPasswordValid($user, $plainOldPassword)) {

                $plainNewPassword = $form->get('newPassword')->getData();
                $newEncoded = $hasher->hashPassword($user, $plainNewPassword);
                $user->setPassword($newEncoded);

                $this->em->flush();

                $this->addFlash('success', $translator->trans('Password changed.'));

                return $this->redirectToRoute('home');

            } else {
                $this->addFlash('danger', $translator->trans('Current passwords is not correct.') . ' ' . $translator->trans('The password has not been changed.'));
                return $this->redirectToRoute('self_user_change_pass');
            }
        }

        return $this->render('me_user/change_pass.html.twig', [
            'form' => $form->createView(),
            'randomPasswords' => $randomPasswords,
            'user' => $user
        ]);
    }


    #[Route('/change_email_init', name: 'self_user_change_email_init', methods: ['GET', 'POST'])]
    public function changeEmailAction(Request $request, TranslatorInterface $translator, UserRepository $userRepository, MailerInterface $mailer): Response
    {

        $form = $this->createForm(MeUserEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $newEmail = $form->get('newEmail')->getData();

            if ($newEmail) {
                $requestedUser = $userRepository->find($user);
                $requestedUser->setNewEmail($newEmail);
                $paschangeToken = StringTools::randomString();
                $tokha = md5($paschangeToken);
                $uri = $this->generateUrl('self_user_change_email_confirmation', ['tokha' => $tokha,
                    'id' => $requestedUser->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $requestedUser->setNewEmailToken($paschangeToken);
                $this->em->flush();

                $this->addFlash('success', $translator->trans('The e-mail address change process has started. Check your e-mail now and click the link that verifies the new e-mail address.'));
                $message = (new Email())
                    ->subject($translator->trans('ChangeEmailInformationMailTitle'))
                    ->to($requestedUser->getEmail())
                    ->html(
                        $this->renderView(
                            $this->emailNotificationService->getRightEmailTemplate('changing_email_information.html.twig'),
                            [
                                'newEmail' => $newEmail,
                                'user' => $requestedUser
                            ]
                        ),
                        'text/html'
                    );
                $mailer->send($message);


                $message = (new Email())
                    ->subject($translator->trans('ChangeEmailRequestMailTitle'))
                    ->to($newEmail)
                    ->html(
                        $this->renderView(
                            $this->emailNotificationService->getRightEmailTemplate('change_email_request.html.twig'),
                            [
                                'paschangeTokenHash' => $tokha,
                                'uri' => $uri,
                                'user' => $requestedUser
                            ]
                        ),
                        'text/html'
                    );
                $mailer->send($message);


                return $this->redirectToRoute('home');

            } else {
                $this->addFlash('danger', $translator->trans('Email is required.'));
                return $this->redirectToRoute('self_user_change_email_init');
            }
        }

        return $this->render('me_user/change_email.html.twig', [
            'form' => $form->createView(),
            'currentEmail' => $this->getUser()->getEmail()
        ]);
    }


    #[Route('/change_email_confirmation/i{id}/{tokha}', name: 'self_user_change_email_confirmation', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function changeEmailConfirmation(User $requestedUser, $tokha, Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        if ($requestedUser != $this->getUser()) {
            $this->addFlash('danger', $translator->trans('The request was for a different user than the logged in user.'));
            return $this->redirectToRoute('self_user_change_email_init');
        }
        if ($tokha != md5($requestedUser->getNewEmailToken())) {
            $this->addFlash('danger', $translator->trans('The request is out of date or has expired.'));
            return $this->redirectToRoute('self_user_change_email_init');
        }
        $formData = ['newEmail' => $requestedUser->getNewEmail()];
        $form = $this->createForm(MeUserEmailConfirmationType::class, $formData);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            if ($requestedUser->getNewEmail()) {
                $requestedUser = $userRepository->find($user);
                $requestedUser->setEmail($requestedUser->getNewEmail());
                $requestedUser->setNewEmail(null);
                $requestedUser->setNewEmailToken(null);

                $this->em->flush();

                $this->addFlash('success', $translator->trans('The email address has been changed.'));

                return $this->redirectToRoute('self_user_show');

            }
        }

        return $this->render('me_user/change_email_confirmation.html.twig', [
            'form' => $form->createView(),
            'currentEmail' => $this->getUser()->getEmail()
        ]);
    }

}
