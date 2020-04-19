<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\SimpleSearchType;
use App\Service\AdminService;
use App\Service\UserService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/admin/users", name="admin_users_show")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param RouterInterface $router
     * @return Response
     */
    public function showUsers(PaginatorInterface $paginator, Request $request, RouterInterface $router): Response
    {
        $usersRepository = $this->getDoctrine()->getRepository(User::class);

        $q = $request->get('q');

        if (!$q) {
            $usersQuery = $usersRepository->createQueryBuilder('u')
                ->getQuery();
        } else {
            $usersQuery = $usersRepository->createQueryBuilder('u')
                ->select('u')
                ->where('u.nickname like :nickname')
                ->orWhere('u.email like :email')
                ->setParameter('nickname', '%'.$q.'%')
                ->setParameter('email', '%'.$q.'%')
                ->getQuery();
        }

        $searchForm = $this->createForm(SimpleSearchType::class, ['query' => $q]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $query = $searchForm->get('query')->getData();

            return new RedirectResponse($router->generate('admin_users_show', ['q' => $query]));
        }

        $users = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            7
        );

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/users/status/change/{userId}",
     *     name="admin_user_change-status",
     *     requirements={"userId"="\d+"})
     * @param UserService $service
     * @param int $userId
     * @return Response
     */
    public function changeUserStatus(UserService $service, int $userId): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);

        $service->changeStatus($user);
        if ($user->getIsActive()) {
            $this->addFlash('success', 'Изменен статус пользователя \'' . $user->getNickname() . '\' на \'Активен\'');
        } else {
            $this->addFlash('success', 'Изменен статус пользователя \'' . $user->getNickname() . '\' на \'Не активен\' ');
        }
        return $this->redirectToRoute('admin_users_show');
    }

    /**
     * @Route("/admin/users/appoint-as-moder/{userId}",
     *     name="admin_user_appoint-as-moder",
     *     requirements={"userId"="\d+"})
     * @param AdminService $service
     * @param int $userId
     * @return Response
     */
    public function appointAsModer(AdminService $service, int $userId): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
        $service->appointAsModer($user);
        $this->addFlash('success', 'Пользователь \'' . $user->getNickname() . '\' теперь модератор');
        return $this->redirectToRoute('admin_users_show');
    }

    /**
     * @Route("/admin/users/reject-moder/{userId}",
     *     name="admin_user_reject-moder",
     *     requirements={"userId"="\d+"})
     * @param AdminService $service
     * @param int $userId
     * @return Response
     */
    public function rejectModer(AdminService $service, int $userId): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
        $service->rejectModer($user);
        $this->addFlash('success', 'Пользователь \'' . $user->getNickname() . '\' теперь не модератор');
        return $this->redirectToRoute('admin_users_show');
    }
}
