<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Form\OrganizationType;
use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/kadmin/organization")
 */
class OrganizationAdminController extends AbstractController
{
    /**
     * @Route("/", name="organization_admin_index", methods={"GET"})
     */
    public function index(OrganizationRepository $organizationRepository): Response
    {
        return $this->render('organization_admin/index.html.twig', [
            'organizations' => $organizationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="organization_admin_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $organization = new Organization();
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($organization);
            $entityManager->flush();

            return $this->redirectToRoute('organization_admin_index');
        }

        return $this->render('organization_admin/new.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="organization_admin_show", methods={"GET"})
     */
    public function show(Organization $organization): Response
    {
        return $this->render('organization_admin/show.html.twig', [
            'organization' => $organization,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="organization_admin_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Organization $organization): Response
    {
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('organization_admin_index');
        }

        return $this->render('organization_admin/edit.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="organization_admin_delete", methods={"POST"})
     */
    public function delete(Request $request, Organization $organization): Response
    {
        if ($this->isCsrfTokenValid('delete'.$organization->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($organization);
            $entityManager->flush();
        }

        return $this->redirectToRoute('organization_admin_index');
    }
}
