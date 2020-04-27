<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserAvatarType;
use App\Form\UserType;
use App\Service\FileUploader;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="app.profile")
     */
    public function index()
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

        return $this->redirectToRoute('app.profile.info', ['nickname' => $user->getNickname()]);
    }

    /**
     * @Route("/profile/{nickname}", name="app.profile.info")
     * @param string $nickname
     * @param UserService $userService
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param FileUploader $fileUploader
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function profileInfo
    (
        string $nickname,
        UserService $userService,
        EntityManagerInterface $em,
        Request $request,
        FileUploader $fileUploader,
        PaginatorInterface $paginator
    )
    {
        /** @var User $user*/
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['nickname' => $nickname]);

        // Checking is user exists
        if (!$user) {
            // TODO: 404 page
            throw $this->createNotFoundException();
        }

        $usersQuizes = $user->getQuizzes();

        // Checking that is your profile and setting unfinished games
        $unfinishedGames = 0;
        $isYou = false;
        if ($user == $this->getUser() && $user->getIsActive()) {
            $isYou = true;
            $unfinishedGames = $userService->getUnfinishedGames($user);
        }

        // Average rating for all user's quizes
        $userRating = $userService->getUserRating($user);

        // Form for nickname change
        $formNickname = $this->createForm(UserType::class, $user);
        $formNickname->handleRequest($request);
        if ($formNickname->isSubmitted() && !$formNickname->get('nickname')->isValid()) {
            $this->addFlash('danger' ,'Ошибка изменения имени профиля. Имя профиля не соответствует требованиям');
        } elseif ($formNickname->isSubmitted() && $formNickname->get('nickname')->isValid())
        {
            $nickname = $formNickname->get('nickname')->getData();

            if ($this->getDoctrine()->getRepository(User::class)->findOneBy(['nickname' => $nickname])) {
                $this->addFlash('danger', 'Никнейм занят');

                return $this->redirectToRoute('app.profile');
            }

            $user->setNickname($nickname);
            $em->flush();
            $this->addFlash('success', 'Имя профиля изменено');
            return $this->redirectToRoute('app.profile');
        }

        // Form for image change
        $formImage = $this->createForm(UserAvatarType::class);
        $formImage->handleRequest($request);
        if ($formImage->isSubmitted() && !$formImage->isValid()) {
            $this->addFlash('danger', 'Ошибка загрузки файла');
        } else if ($formImage->isSubmitted() && $formImage->isValid()) {

            if ($user != $this->getUser()) {
                return $this->redirectToRoute('app_login');
            }

            $image = $formImage->get('image')->getData();
            $file = new UploadedFile($image, 'fileName');

            $fileUploader->uploadAvatar($file, $user);

            $this->addFlash('success', 'Фото профиля изменено');
            return $this->redirectToRoute('app.profile');
        }

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'user' => $user,
            'isYou' => $isYou,
            'userRating' => $userRating,
            'usersQuizes' => $usersQuizes,
            'unfinishedGames' => $unfinishedGames,
            'formNickname' => $formNickname->createView(),
            'formImage' => $formImage->createView(),
        ]);
    }
}
