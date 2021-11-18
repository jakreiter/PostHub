<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use App\Repository\LetterRepository;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Security as CoreSecurity;



/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{

    /**
     * @Route("/", name="home", methods="GET")
     * @Security("is_granted('ROLE_USER')")
     */

    public function homeAction(Request $request, SluggerInterface $slugger, LetterRepository $letterRepository, OrganizationRepository $organizationRepository, CoreSecurity $security): Response
    {
        if ('https'==$_ENV['WEB_APP_SCHEME'] && 'http'==$request->getScheme()) {
            $appUrl = $_ENV['WEB_APP_SCHEME'].'://'.$_ENV['WEB_APP_HOST'].$_ENV['WEB_APP_BASE_URL'];
            return $this->redirect($appUrl);
        }

        /** @var User $user */
        $user = $this->getUser();

        $numberOfUnseenPerOrganization = [];
        $defaultOrganizationId = null;
        if ($security->isGranted('ROLE_LOCATION_MODERATOR')) {
            // letter_admin_index for ADMIN
            return $this->redirectToRoute('letter_admin_index', ['sort' => 'Letter.created', 'direction'=>'desc']);
        }
        else {
            $organizations = $user->getOrganizations();
            if (count($organizations) > 1) {
                foreach ($organizations as $organization) {
                    $numberOfUnseenPerOrganization[$organization->getId()] = $letterRepository->getNumberOfUnseenPerOrganization($organization);
                }
                $maxUnseen = max($numberOfUnseenPerOrganization);
                foreach ($numberOfUnseenPerOrganization as $organizationId => $numberOfUnseen) {
                    if ($numberOfUnseen === $maxUnseen) $defaultOrganizationId = $organizationId;
                }

            } else if (1 === count($organizations)) {
                $defaultOrganizationId = $organizations[0]->getId();
            }
            if ($defaultOrganizationId) {
                return $this->redirectToRoute('letter_user_index', ['id' => $defaultOrganizationId]);
            }
        }
        return $this->render('home.html.twig', []);
    }


}