<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RecoveryPasswordEmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class RecoveryPasswordController extends AbstractController
{
    /**
     * @Route("/recovery/", name="recovery_password")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function index(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(RecoveryPasswordEmailType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $form->get('email')->getData()]);

            if (!$user) {
                throw $this->createNotFoundException(
                    'No user found with email ' . $form->get('email')->getData()
                );
            }

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('example@example.com')
                ->setTo($user->getEmail())
                ->setBody('Your password: ' . $user->getPassword(), 'text/plain')
            ;

            $mailer->send($message);
            // TODO: Add flash, redirect to login page
        }

        return $this->render('recovery_password/index.html.twig', [
            'controller_name' => 'RecoveryPasswordController',
            'form' => $form->createView(),
        ]);
    }
}
