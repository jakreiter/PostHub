<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

	/**
	 * @Route("/login", name="login")
	 */
	public function login(Request $request, AuthenticationUtils $authenticationUtils)
	{
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		

		
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();
		
		return $this->render('security/login.html.twig', array(
			'last_username' => $lastUsername,
			'error' => $error
		));
	}

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

}
