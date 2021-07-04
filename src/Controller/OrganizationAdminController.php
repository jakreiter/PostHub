<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Organization;
use App\Form\OrganizationType;
use App\Form\Filter\OrganizationFilterType;
use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * @Route("/kadmin/organization")
 */
class OrganizationAdminController extends AbstractController
{
    /**
     * @Route("/find.{_format}",
     *      requirements = { "_format" = "html|json" },
     *      name="organization_admin_find", methods={"GET"})
     */
    public function findByFragmentAction(Request $request, $_format, OrganizationRepository $organizationRepository): Response
    {
        $fragment = $request->query->get('q');
        if ($fragment) {
            $organizations = $organizationRepository->findByFragment($fragment);
        } else {
            $organizations = [];
        }

        if ('html' == $_format) {
            return $this->render('organization_admin/index.html.twig', [
                'organizations' => $organizations,
            ]);
        } else {
            $orgArrs = [];
            if (count($organizations)) {
                foreach ($organizations as $organization) {
                    $orgArrs[]=$organization->toArray();
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
     * @Route("/", name="organization_admin_index", methods={"GET"})
     */
    public function index(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $filterForm = $this->createForm(OrganizationFilterType::class);

        $filterBuilder = $em->createQueryBuilder()
            ->select([
                'Organization', 'Owner', 'Location', 'ScanPlan'
            ])
            ->from('App\Entity\Organization', 'Organization')
            ->leftJoin('Organization.owner', 'Owner')
            ->leftJoin('Organization.location', 'Location')
            ->leftJoin('Organization.scanPlan', 'ScanPlan')
            ;


        if ($request->query->has($filterForm->getName())) {
            $filter = $request->query->get($filterForm->getName());
            $filterForm->submit($filter);

            if (isset($filter['name']) && $filter['name']) {
                $filterBuilder->andWhere('Organization.name LIKE :name')->setParameter('name', '%'.$filter['name'].'%');
            }
            if (isset($filter['location']) && $filter['location']) {
                $filterBuilder->andWhere('Location.id = :location')->setParameter('location', $filter['location']);
            }


        }

        $query = $filterBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $_ENV['PAGINATION_MAX_NUMBER_OF_ITEM_PER_PAGE'] /*limit per page*/
        );

        $organizations = $query->getResult();

        return $this->render('organization_admin/index.html.twig', [
            'organizations' => $organizations,
            'pagination' => $pagination,
            'form' => $filterForm->createView(),
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
        if ($this->isCsrfTokenValid('delete' . $organization->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($organization);
            $entityManager->flush();
        }

        return $this->redirectToRoute('organization_admin_index');
    }
}
