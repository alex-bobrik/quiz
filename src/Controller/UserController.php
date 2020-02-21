<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\User;
use App\Service\UserService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/admin/users", name="admin_users_show")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function showUsers(PaginatorInterface $paginator, Request $request): Response
    {
        $usersRepository = $this->getDoctrine()->getRepository(User::class);

        $usersQuery = $usersRepository->createQueryBuilder('u')
            ->getQuery();

        $users = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            7
        );

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
        ]);
    }

    /**
     * @Route("/admin/users/status/change/{userId}", name="admin_user_change-status")
     * @param UserService $service
     * @param int $userId
     * @return Response
     */
    public function changeUserStatus(UserService $service, int $userId): Response
    {
        $service->changeStatus($userId);
        return $this->redirectToRoute('admin_users_show');
    }
}
