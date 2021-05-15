<?php

namespace App\Controller;

use App\Entity\PassResetEmail;
use App\Entity\PassSetter;
use App\Entity\User;
use App\Form\PassResetEmailType;
use App\Form\UserPassSetType;
use App\Model\PassHelperTools;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Model\StringTools;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Service\RecaptchaService;

/**
 * @Route("/pass_reset")
 */
class PassResetController extends AbstractController
{


    /**
     * @Route("/", name="pass_reset_email", methods="GET|POST")
     * @param Request $request
     * @return Response
     */
    public function resetRequest(Request $request, UserRepository $userRepository, MailerInterface $mailer,
                                 TranslatorInterface $translator, RecaptchaService $recaptchaService, ParameterBagInterface $parameterBag): Response
    {
        $passResetEmail = new PassResetEmail();
        $form = $this->createForm(PassResetEmailType::class, $passResetEmail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            $remoteIp = $_SERVER['REMOTE_ADDR'];
            $humanProbability = $recaptchaService->recaptchaGetProbabilityThatUserIsHuman($recaptchaResponse, $remoteIp);

            $recaptchaScoreThreshold = $parameterBag->get('recaptcha_score_threshold');

            if ($humanProbability < $recaptchaScoreThreshold) {
                $this->addFlash('danger', "You have been identified as a non-human being.<br> You can try to do some human activities, like visiting macrobond.com or using Google services, and then try again.");
            } else {

                $requestedUser = $userRepository->findOneByEmail($passResetEmail->getEmail());
                if ($requestedUser) {
                    $now = new \DateTime();
                    if ($requestedUser->getLastPassResetRequest()) {
                        $daysFromLast = $now->diff($requestedUser->getLastPassResetRequest())->days;
                        if ($daysFromLast < 1) {
                            $this->addFlash('danger', $translator->trans('lastRequestTooClose'));
                            return $this->redirectToRoute('home');
                        }
                    }
                    /**
                     * @var User $requestedUser
                     */

                    //$requestedUser->getLastPassResetRequest()
                    $requestedUser->setLastPassResetRequest(new \DateTime());
                    $paschangeToken = StringTools::randomString();
                    $requestedUser->setPassResetToken($paschangeToken);

                    $tokha = md5($paschangeToken);
                    $uri = $this->generateUrl('pass_reset_set_password', ['tokha' => $tokha,
                        'id' => $requestedUser->getId()
                    ], UrlGeneratorInterface::ABSOLUTE_URL);

                    $message = (new Email())
                        ->subject($translator->trans('ResetPasswordRequestMailTitle'))
                        ->to($passResetEmail->getEmail())
                        ->html(
                            $this->renderView(
                                'emails/pass_reset__request.html.twig',
                                [
                                    'paschangeTokenHash' => $tokha,
                                    'uri' => $uri,
                                    'user' => $requestedUser
                                ]
                            ),
                            'text/html'
                        );

                    $mailer->send($message);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($requestedUser);
                    $em->flush();


                    $this->addFlash('success', $translator->trans("Password reset request sent."));
                    return $this->redirectToRoute('home');
                }
                $this->addFlash('danger', $translator->trans("Password reset request sent."));
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('pass_reset/make_request.twig', [
            'passResetEmail' => $passResetEmail,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/i{id}/{tokha}", name="pass_reset_set_password", methods="GET|POST", requirements={
     * "id": "\d+"
     * })
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param User $user
     * @param $tokha
     * @return Response
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder, $id, $tokha,
                                   UserRepository $userRepository, TranslatorInterface $translator,
                                   MailerInterface $mailer): Response
    {
        $rtp = $request->get('rtp');
        if ('new' == $rtp) $thisIsPasswordSettingForTheNewAccount = true;
        else $thisIsPasswordSettingForTheNewAccount = false;

        $user = $userRepository->findOneById($id);
        /**
         * @var User $user
         */
        $now = new \DateTime();

        for ($i = 0; $i < 10; $i++) {
            $randomPasswords[] = PassHelperTools::randomString();
        }
        $randomPasswords[] = StringTools::randomString() . StringTools::randomString() . StringTools::randomString();

        if ($user && $tokha && $user->getLastPassResetRequest() && $now->diff($user->getLastPassResetRequest())->days < 1 && $tokha == md5($user->getPassResetToken())) {

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


                    if ($thisIsPasswordSettingForTheNewAccount) {
                        // alert Admin
                        $uri = $this->generateUrl('user_show', ['id' => $user->getId()],
                            UrlGeneratorInterface::ABSOLUTE_URL);

                        $message = (new Email())
                            ->subject($translator->trans('New forum user'))
                            ->to($_ENV['ADMIN_CONTACT_EMAIL'])
                            ->html(
                                $this->renderView(
                                    'emails/new_user_admin_alert.html.twig',
                                    [
                                        'uri' => $uri,
                                        'user' => $user
                                    ]
                                ),
                                'text/html'
                            );

                        $mailer->send($message);
                    }

                    $this->addFlash('success', $translator->trans('Password changed.'));
                    return $this->redirectToRoute('login');
                } else {
                    $this->addFlash('danger', $translator->trans('New passwords not match.'));
                }
                return $this->redirectToRoute('pass_reset_set_password', ['id' => $id, 'tokha' => $tokha
                ]);
            }

            return $this->render('me_user/set_pass.html.twig', [
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
}