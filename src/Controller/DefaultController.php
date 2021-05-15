<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\LetterType;
use App\Entity\Letter;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{

    /**
     * @Route("/", name="home", methods="GET")
     */

    public function newAction(Request $request, SluggerInterface $slugger): Response
    {

        return $this->render('home.html.twig', []);
    }


}