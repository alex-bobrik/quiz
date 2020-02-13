<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="account")
     */
    public function index(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')){

            $repository = $this->getDoctrine()->getRepository(User::class);

            $products = $repository->findAll();

            return $this->render('account/index.html.twig', [
                'controller_name' => 'AccountController',
                'prods' => $products,
            ]);
        }

        return new Response('<h1>Only for admin</h1>');
    }
}
