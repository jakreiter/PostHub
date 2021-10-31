<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Organization;
use App\Form\OrganizationSelfType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("org_")
 */
class OrganizationUserController extends AbstractController
{
    private $em;
    private $environment;

    public function __construct(EntityManagerInterface $em, $environment)
    {
        $this->em = $em;
        $this->environment = $environment;

    }

    /**
     * @Route("{id}/edit", name="organization_user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Organization $organization): Response
    {

        if ($organization->getOwner()!=$this->getUser()) {
            $response = $this->render('security/access_denied.html.twig');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }

        $form = $this->createForm(OrganizationSelfType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('letter_user_index', ['id'=>$organization->getId()]);
        }

        return $this->render('organization_user/edit.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
        ]);
    }

}
