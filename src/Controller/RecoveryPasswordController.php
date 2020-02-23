<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RecoveryPasswordEmailType;
use App\Form\RecoveryPasswordType;
use App\Service\TokenGenerator;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class RecoveryPasswordController extends AbstractController
{
    /**
     * @Route("/recovery/", name="recovery_password")
     * @param TokenGenerator $tokenGenerator
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function recoveryPassword(TokenGenerator $tokenGenerator, Request $request, \Swift_Mailer $mailer): Response
    {
       if ($this->getUser()){
           throw $this->createNotFoundException('Access denied');
       }

        $form = $this->createForm(RecoveryPasswordEmailType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $form->get('email')->getData()]);

            if (!$user) {
                $this->addFlash('info', 'No user found with this email');

                return $this->redirectToRoute('recovery_password');
            }

            // generate token and save user
            $user->setToken($tokenGenerator->getToken());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();


            //generate and send messages to the mail
            $message = (new \Swift_Message('Password recovery'))
                ->setFrom('example@example.com')
                ->setTo($user->getEmail())
                ->setBody('http://quiz.test:90/recovery/' . $user->getToken());

            $mailer->send($message);

            $this->addFlash('info', 'Email was send');

            return $this->render('recovery_password/index.html.twig', [
                'form' => $form->createView(),
            ]);

        }

        return $this->render('recovery_password/index.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/recovery/{token}", name="recovery_password_end")
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @param UserService $userService
     * @param \Swift_Mailer $mailer
     * @param string $token
     * @return Response
     */
    public function recoveryPasswordEnd(UserPasswordEncoderInterface $encoder, Request $request, UserService $userService, \Swift_Mailer $mailer, string $token)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['token' => $token]);

        $form = $this->createForm(RecoveryPasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $user->setToken('');
            $userService->saveUser($encoder, $user);

            return $this->redirectToRoute('app_login');
        }

        return $this->render(
            'recovery_password/recovery_end.html.twig', [
                'form' => $form->createView(),
        ]);
    }
}
