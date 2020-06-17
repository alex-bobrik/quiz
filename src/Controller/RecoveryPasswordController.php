<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RecoveryPasswordEmailType;
use App\Form\RecoveryPasswordType;
use App\Service\MailSender;
use App\Service\TokenGenerator;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RecoveryPasswordController extends AbstractController
{
    /**
     * @Route("/recovery/", name="recovery_password")
     * @param TokenGenerator $tokenGenerator
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function recoveryPassword
    (
        TokenGenerator $tokenGenerator,
        Request $request,
        MailSender $mailer
    ): Response
    {
       if ($this->getUser()){
           return $this->redirectToRoute('games_show');
       }

        $form = $this->createForm(RecoveryPasswordEmailType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $form->get('email')->getData()]);

            if (!$user) {
                $this->addFlash('info', 'Не найдено пользователя с заданным email');

                return $this->redirectToRoute('recovery_password');
            }

            // generate token and save user
            $user->setToken($tokenGenerator->getToken());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $mailer->send(
                'Восстановление пароля',
                $user->getEmail(),
                'http://salty-cove-86547.herokuapp.com/recovery/' . $user->getToken()
            );

            $this->addFlash('info', 'Сообщение отправлено');

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
     * @param string $token
     * @return Response
     */
    public function recoveryPasswordEnd
    (
        UserPasswordEncoderInterface $encoder,
        Request $request,
        UserService $userService,
        string $token
    ): Response
    {
        /** @var User $user */
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['token' => $token]);

        if (!$user || !$user->getIsActive()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RecoveryPasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            $user->setToken('');
            $userService->saveUserAfterRecovery($encoder, $user);

            $this->addFlash('success', 'Пароль успешно изменен');

            return $this->redirectToRoute('app_login');
        }

        return $this->render(
            'recovery_password/recovery_end.html.twig', [
                'form' => $form->createView(),
        ]);
    }
}
