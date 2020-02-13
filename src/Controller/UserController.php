<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/admin/users", name="admin_users_show")
     */
    public function showUsers(): Response
    {
        $repository = $this->getDoctrine()->getRepository(User::class);

        $users = $repository->findAll();

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
        ]);
    }

    /**
     * @Route("/admin/users/status/change/{id}", name="admin_user_change-status")
     * @param UserService $service
     * @param int $id
     * @return Response
     */
    public function changeUserStatus(UserService $service, int $id): Response
    {
        $service->changeStatus($id);
        return $this->redirectToRoute('admin_users_show');
    }
}
