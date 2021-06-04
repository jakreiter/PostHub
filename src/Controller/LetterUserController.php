<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\Filter\LetterUserFilterType;
use App\Entity\Letter;
use App\Entity\Organization;

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
        if ($organization->getOwner()!=$user) {
            $response = $this->render('security/access_denied.html.twig');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }

        $filterForm = $this->createForm(LetterUserFilterType::class);

        $filterBuilder = $em->createQueryBuilder()
            ->select([
                'Letter',  'LetterStatus'
            ])
            ->from('App\Entity\Letter', 'Letter')
            ->leftJoin('Letter.status', 'LetterStatus')
            ->addOrderBy('Letter.created', 'DESC')
            ;
        ;
        $filterBuilder->andWhere('Letter.organization = :organization')->setParameter('organization', $organization);

        if ($request->query->has($filterForm->getName())) {
            $filter = $request->query->get($filterForm->getName());
            $filterForm->submit($filter);

            if (isset($filter['title']) && $filter['title']) {
                $filterBuilder->andWhere('Letter.title LIKE :title')->setParameter('title', '%'.$filter['title'].'%');
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
            'organization'=>$organization
        ]);
    }


    /**
     * @Route("{organization}/letter_show:{letter_id}", name="letter_user_show", methods={"GET"}, requirements={
     * "organization_id": "\d+"
     * })
     */
    public function show(Organization $organization, Letter $letter): Response
    {
        $user = $this->getUser();
        if ($organization->getOwner()!=$user) {
            $response = $this->render('security/access_denied.html.twig');
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }

        return $this->render('letter_user/show.html.twig', [
            'letter' => $letter,
        ]);
    }





}