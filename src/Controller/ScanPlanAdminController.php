<?php

namespace App\Controller;

use App\Entity\ScanPlan;
use App\Form\ScanPlanType;
use App\Repository\ScanPlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("kadmin/scan_plan")
 */
class ScanPlanAdminController extends AbstractController
{
    /**
     * @Route("/", name="scan_plan_admin_index", methods={"GET"})
     */
    public function index(ScanPlanRepository $scanPlanRepository): Response
    {
        return $this->render('scan_plan_admin/index.html.twig', [
            'scan_plans' => $scanPlanRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="scan_plan_admin_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $scanPlan = new ScanPlan();
        $form = $this->createForm(ScanPlanType::class, $scanPlan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($scanPlan);
            $entityManager->flush();

            return $this->redirectToRoute('scan_plan_admin_index');
        }

        return $this->render('scan_plan_admin/new.html.twig', [
            'scan_plan' => $scanPlan,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="scan_plan_admin_show", methods={"GET"})
     */
    public function show(ScanPlan $scanPlan): Response
    {
        return $this->render('scan_plan_admin/show.html.twig', [
            'scan_plan' => $scanPlan,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="scan_plan_admin_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ScanPlan $scanPlan): Response
    {
        $form = $this->createForm(ScanPlanType::class, $scanPlan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('scan_plan_admin_index');
        }

        return $this->render('scan_plan_admin/edit.html.twig', [
            'scan_plan' => $scanPlan,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="scan_plan_admin_delete", methods={"POST"})
     */
    public function delete(Request $request, ScanPlan $scanPlan): Response
    {
        if ($this->isCsrfTokenValid('delete'.$scanPlan->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($scanPlan);
            $entityManager->flush();
        }

        return $this->redirectToRoute('scan_plan_admin_index');
    }
}
