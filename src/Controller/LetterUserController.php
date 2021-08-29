<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\LetterOrderScanType;
use App\Form\Filter\LetterUserFilterType;
use App\Entity\Letter;
use App\Entity\Organization;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/org_")
 */
class LetterUserController extends AbstractController
{
    /**
     * @Route("{id}/letters", name="letter_user_index", methods={"GET"})
     */
    public function index(Organization $organization, EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {

        $user = $this->getUser();
        if ($organization->getOwner() != $user) {
            $response = $this->render('security/access_denied.html.twig');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }

        $filterForm = $this->createForm(LetterUserFilterType::class);

        $filterBuilder = $em->createQueryBuilder()
            ->select([
                'Letter', 'LetterStatus'
            ])
            ->from('App\Entity\Letter', 'Letter')
            ->leftJoin('Letter.status', 'LetterStatus')
            ->addOrderBy('Letter.created', 'DESC');;
        $filterBuilder->andWhere('Letter.organization = :organization')->setParameter('organization', $organization);

        if ($request->query->has($filterForm->getName())) {
            $filter = $request->query->get($filterForm->getName());
            $filterForm->submit($filter);

            if (isset($filter['title']) && $filter['title']) {
                $filterBuilder->andWhere('Letter.title LIKE :title')->setParameter('title', '%' . $filter['title'] . '%');
            }
            if (isset($filter['status']) && $filter['status']) {
                $filterBuilder->andWhere('LetterStatus.id = :status')->setParameter('status', $filter['status']);
            }
            if (isset($filter['barcodeNumber']) && $filter['barcodeNumber']) {
                $filterBuilder->andWhere('Letter.barcodeNumber = :barcodeNumber')->setParameter('barcodeNumber', $filter['barcodeNumber']);
            }

        }

        $query = $filterBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            $_ENV['PAGINATION_MAX_NUMBER_OF_ITEM_PER_PAGE'] /*limit per page*/
        );

        $letters = $query->getResult();

        return $this->render('letter_user/index.html.twig', [
            'letters' => $letters,
            'pagination' => $pagination,
            'form' => $filterForm->createView(),
            'organization' => $organization
        ]);
    }

    /**
     * @Route("{organization}/letter_order_scan:{letter}/", name="letter_user_order_scan", methods={"GET","POST"}, requirements={
     * "organization": "\d+"
     * })
     */
    public function orderScanAction(Request $request, Letter $letter, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(LetterOrderScanType::class, $letter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($letter->getScanOrdered()) {
                $this->addFlash('warning', $translator->trans('You cannot order a scan because it has already been ordered.').' ('.$letter->getScanOrdered()->format('Y-m-d H:i').')');
            } else if ($letter->getOrganization()->getScan()) {
                $this->addFlash('warning', $translator->trans('You cannot order a scan because we scan all correspondence for your account.'));
            } else if ($letter->getFileName()) {
                $this->addFlash('warning', $translator->trans('You cannot order a scan of this letter as the scan is already in the system.'));
            } else {
                $letter->setModifiedByUser($this->getUser());
                $letter->setScanOrdered(new \DateTime());
                $letter->setScanDue($_ENV['SINGLE_SCAN_PRICE']);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $translator->trans('A scan of the letter has been ordered.'));
                //return $this->redirectToRoute('letter_user_index', ['id' => $letter->getOrganization()->getId()]);
            }
        }

        return $this->render('letter_user/order_scan.html.twig', [
            'letter' => $letter,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("{organization}/letter_show:{letter}/", name="letter_user_show", methods={"GET"}, requirements={
     * "organization": "\d+"
     * })
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Organization $organization, Letter $letter): Response
    {
        $user = $this->getUser();
        if ($organization->getOwner() != $user) {
            $response = $this->render('security/access_denied.html.twig');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }

        return $this->render('letter_user/show.html.twig', [
            'letter' => $letter,
        ]);
    }


}