<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Organization;
use App\Form\OrganizationType;
use App\Form\Filter\OrganizationFilterType;
use App\Form\Filter\ScanReportFilterType;
use App\Repository\OrganizationRepository;
use App\Service\EmailNotificationService;
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
    private $em;
    private $environment;

    public function __construct(EntityManagerInterface $em, $environment)
    {
        $this->em = $em;
        $this->environment = $environment;

    }

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
                    $orgArrs[] = $organization->toArray();
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
     * @Route("/scan_report", name="scan_report", methods={"GET"})
     */
    public function scanReportPerOrganization(PaginatorInterface $paginator, Request $request): Response
    {
        $defaultData= [
            'orderedFrom'=> new \DateTime('first day of last month'),
            'orderedTill'=> new \DateTime('last day of last month'),
        ];
        $filterForm = $this->createForm(ScanReportFilterType::class, $defaultData);

        $filterBuilder = $this->orgFilter($request, $filterForm);

        $query = $filterBuilder->getQuery();


        $organizations = $query->getResult();



        if ($organizations && count($organizations)) {
            //if ('dev' == $this->environment) dump($organizations);

            if ($request->query->has($filterForm->getName())) {
                $filter = $request->query->get($filterForm->getName());
                //if ('dev' == $this->environment) dump($filter);
                $orderedFrom = $filter['orderedFrom'];
                $orderedTill = $filter['orderedTill'];
            } else {
                $orderedFrom = $defaultData['orderedFrom'];
                $orderedTill = $defaultData['orderedTill'];
                $orderedFrom->setTime(0,0,0);
                $orderedTill->setTime(23,59,59);

            }
            if ('dev' == $this->environment) {
                //dump($orderedFrom);
                //dump($orderedTill);
            }

            $em = $this->em;

                $query = $em->createQuery('SELECT organization.id,
                                            COUNT(letter.id) AS letters, 
                                            SUM(letter.scanDue) AS due 
                                            FROM App\Entity\Letter letter
                                            LEFT JOIN letter.organization organization
                                            INDEX BY organization.id 
                                            WHERE (letter.organization IN (:organizations))      
                                            AND letter.scanOrdered IS NOT NULL
                                            AND letter.scanOrdered BETWEEN :orderedFrom AND :orderedTill 
                                            GROUP BY organization.id
                                            ')
                                            ->setParameter('organizations', $organizations)
                                            ->setParameter('orderedFrom', $orderedFrom)
                                            ->setParameter('orderedTill', $orderedTill);
            $lettersPerOrgs = $query->getResult();
            if ('dev' == $this->environment) {
                dump($lettersPerOrgs);
            }
            foreach ($organizations as $idOrg=>$organization) {
                if (!isset($lettersPerOrgs[$idOrg])) unset($organizations[$idOrg]);
            }

            /*
            $lettersPerOrgs = $scanReportQueryBuilder = $em->createQueryBuilder()
                ->select([ 'Organization.id', 'COUNT(Letter)'])
                ->from('App\Entity\Letter', 'Letter')
                ->leftJoin('Letter.organization', 'Organization')
                ->andWhere('Letter.organization IN (:organizations)')->setParameter('organizations', $organizations)
                ->groupBy('Letter.organization')
                //->indexBy('Organization','Organization.id')
                ->getQuery()
                ->getResult()
            ;
            if ('dev' == $this->environment) {
                dump($lettersPerOrgs);
            }
            */

        }


        return $this->render('organization_admin/scan_report.html.twig', [
            'organizations' => $organizations,
            'lettersPerOrgs'=> $lettersPerOrgs,
            'form' => $filterForm->createView(),
        ]);

    }

    /**
     * @Route("/notification_required", name="organization_notification_required", methods={"GET"})
     */
    public function RequiredNotificationPerOrganizationAction(PaginatorInterface $paginator, Request $request, EmailNotificationService $emailNotificationService): Response
    {
        $requiringNotificationInfoPerOrganization = $emailNotificationService->getNumberOfRequiringNewLetterNotificationPerOrganization();
        $orgIds =  array_keys($requiringNotificationInfoPerOrganization);
        if ('dev' == $this->environment) dump($requiringNotificationInfoPerOrganization);



        $em=$this->em;
        $filterBuilder = $em->createQueryBuilder()
            ->select([
                'Organization', 'Owner', 'Location', 'ScanPlan'
            ])
            ->from('App\Entity\Organization', 'Organization')
            ->leftJoin('Organization.owner', 'Owner')
            ->leftJoin('Organization.location', 'Location')
            ->leftJoin('Organization.scanPlan', 'ScanPlan')
            ->indexBy('Organization','Organization.id')
        ;
        $filterBuilder->andWhere('Organization.id IN (:orgIds)')->setParameter('orgIds', $orgIds);
        $query = $filterBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $_ENV['PAGINATION_MAX_NUMBER_OF_ITEM_PER_PAGE'] /*limit per page*/
        );

        $organizations = $query->getResult();

        return $this->render('organization_admin/requiring_notification.html.twig', [
            'organizations' => $organizations,
            'pagination' => $pagination,
            'requiringNotificationInfoPerOrganization' => $requiringNotificationInfoPerOrganization
        ]);

    }


    /**
     * @Route("/send_notifications", name="organization_admin_send_notifications", methods={"GET"})
     */
    public function sendNotifications(Request $request, EmailNotificationService $emailNotificationService): Response
    {
        $notifications = $emailNotificationService->sendNewLettersNotifications(30);

        return $this->render('organization_admin/notifications_sent.html.twig', [
            'notifications'=>$notifications
        ]);

    }


    /**
     * @Route("/", name="organization_admin_index", methods={"GET"})
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $filterForm = $this->createForm(OrganizationFilterType::class);

        $filterBuilder = $this->orgFilter($request, $filterForm);

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
     * @Route("/{id}/notifications", name="organization_notifications_admin_show", methods={"GET"})
     */
    public function showNotifications(Organization $organization): Response
    {
        return $this->render('organization_admin/notifications.html.twig', [
            'organization' => $organization,
        ]);
    }

    /**
     * @Route("/{organization}/notification/{notification}", name="organization_notification_admin_show", methods={"GET"}, requirements={
     * "organization": "\d+",
     * "notification": "\d+"
     * })
     */
    public function showNotification(Organization $organization, Notification $notification): Response
    {
        if ($notification->getOrganization()!=$organization) {
            $response = $this->render('security/access_denied.html.twig');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }
        return $this->render('organization_admin/notification.html.twig', [
            'organization' => $organization,
            'notification' => $notification,
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

    private function orgFilter(Request $request, \Symfony\Component\Form\FormInterface $filterForm): \Doctrine\ORM\QueryBuilder
    {
        $em = $this->em;
        $filterBuilder = $em->createQueryBuilder()
            ->select([
                 'Organization', 'Owner', 'Location', 'ScanPlan'
            ])
            ->from('App\Entity\Organization', 'Organization')
            ->leftJoin('Organization.owner', 'Owner')
            ->leftJoin('Organization.location', 'Location')
            ->leftJoin('Organization.scanPlan', 'ScanPlan')
            ->indexBy('Organization','Organization.id')
        ;


        if ($request->query->has($filterForm->getName())) {
            $filter = $request->query->get($filterForm->getName());
            $filterForm->submit($filter);

            if (isset($filter['name']) && $filter['name']) {
                $filterBuilder->andWhere('Organization.name LIKE :name')->setParameter('name', '%' . $filter['name'] . '%');
            }
            if (isset($filter['location']) && $filter['location']) {
                $filterBuilder->andWhere('Location.id = :location')->setParameter('location', $filter['location']);
            }
            if (isset($filter['scanPlan']) && $filter['scanPlan']) {
                $filterBuilder->andWhere('ScanPlan.id = :scanPlan')->setParameter('scanPlan', $filter['scanPlan']);
            }
        }
        return $filterBuilder;
    }
}
