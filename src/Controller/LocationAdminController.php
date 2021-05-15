<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/kadmin/location")
 */
class LocationAdminController extends AbstractController
{
    /**
     * @Route("/", name="location_admin_index", methods={"GET"})
     */
    public function index(LocationRepository $locationRepository): Response
    {
        return $this->render('location_admin/index.html.twig', [
            'locations' => $locationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="location_admin_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($location);
            $entityManager->flush();

            return $this->redirectToRoute('location_admin_index');
        }

        return $this->render('location_admin/new.html.twig', [
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="location_admin_show", methods={"GET"})
     */
    public function show(Location $location): Response
    {
        return $this->render('location_admin/show.html.twig', [
            'location' => $location,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="location_admin_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Location $location): Response
    {
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('location_admin_index');
        }

        return $this->render('location_admin/edit.html.twig', [
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="location_admin_delete", methods={"POST"})
     */
    public function delete(Request $request, Location $location): Response
    {
        if ($this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($location);
            $entityManager->flush();
        }

        return $this->redirectToRoute('location_admin_index');
    }
}
