<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserAvatarType;
use App\Form\UserType;
use App\Service\FileUploader;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="app.profile")
     */
    public function index()
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

//        $directory = $this->getParameter('avatars_directory');
//        unlink($directory . $user->getImage());

        return $this->redirectToRoute('app.profile.info', ['nickname' => $user->getNickname()]);
    }

    /**
     * @Route("/profile/{nickname}", name="app.profile.info")
     * @param string $nickname
     * @param UserService $userService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profileInfo(string $nickname, UserService $userService, EntityManagerInterface $em, Request $request, FileUploader $fileUploader)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['nickname' => $nickname]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isYou = false;
        if ($user == $this->getUser() && $user->getIsActive()) {
            $isYou = true;
        }

        $userRating = $userService->getUserRating($user);

        $formNickname = $this->createForm(UserType::class, $user);
        $formNickname->handleRequest($request);
        if ($formNickname->isSubmitted()) {
            $nickname = $formNickname->get('nickname')->getData();

            if ($this->getDoctrine()->getRepository(User::class)->findOneBy(['nickname' => $nickname])) {
                $this->addFlash('info', 'Никнейм занят');

                return $this->redirectToRoute('app.profile');
            }

            $user->setNickname($nickname);
            $em->flush();
            return $this->redirectToRoute('app.profile');
        }

        $formImage = $this->createForm(UserAvatarType::class);
        $formImage->handleRequest($request);
        if ($formImage->isSubmitted() && !$formImage->isValid()) {
            // TODO: file upload error
            throw $this->createAccessDeniedException('invalid');
        } else if ($formImage->isSubmitted() && $formImage->isValid()) {

            $image = $formImage->get('image')->getData();
            $file = new UploadedFile($image, 'fileName');

            $fileUploader->uploadAvatar($file, $user);

            return $this->redirectToRoute('app.profile');
        }

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'user' => $user,
            'isYou' => $isYou,
            'userRating' => $userRating,
            'formNickname' => $formNickname->createView(),
            'formImage' => $formImage->createView(),
        ]);
    }
}
