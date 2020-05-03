<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Service\MailSender;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserService $userService
     * @param MailSender $mailSender
     * @return RedirectResponse|Response
     */
    public function register
    (
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        UserService $userService,
        MailSender $mailSender
    ) : Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getDoctrine()->getRepository(User::class)
                ->findOneBy(['email' => $form->get('email')->getData()]);

            if ($user) {
                $this->addFlash('info', 'Такой пользователь уже существует');
                return $this->redirectToRoute('app_register');
            }

            $user = $form->getData();
            $user->setIsActive(false);
            $userService->saveUser($passwordEncoder, $user);

            $mailSender->send(
                'Подтверждение регистрации',
                $user->getEmail(),
                'http://quiz.work/register/verificate/' . $user->getToken()
            );

            $this->addFlash('info', 'Сообщение отправлено');
            return $this->redirectToRoute('app_register');
        }

        return $this->render(
            'registration/index.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/register/verificate/{token}", name="user_verification")
     * @param UserService $userService
     * @param string $token
     * @return Response
     */
    public function userVerification(UserService $userService, string $token): Response
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['token' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Invalid token');
        }

        $userService->activateUser($user);

        return $this->redirectToRoute('app_login');
    }
}