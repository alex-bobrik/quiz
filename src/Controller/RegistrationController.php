<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="user_registration")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserService $userService
     * @param \Swift_Mailer $mailer
     * @return RedirectResponse|Response
     */
    public function register(Request $request,
                             UserPasswordEncoderInterface $passwordEncoder,
                             UserService $userService,
                            \Swift_Mailer $mailer) : Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user->setIsActive(false);
            $userService->saveUser($passwordEncoder, $user);

            // send mail for verification
            $message = (new \Swift_Message('Profile verification'))
                ->setFrom('example@example.com')
                ->setTo($user->getEmail())
                ->setBody('http://quiz.test:90/register/verificate/' . $user->getToken());

            $mailer->send($message);

            // TODO: Add flash 'Email was send to your email'
            return $this->redirectToRoute('user_registration');
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

        $userService->activateUser($user);

        return $this->redirectToRoute('app_login');
    }
}
