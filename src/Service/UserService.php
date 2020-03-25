<?php declare(strict_types=1);

namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    private $em;

    private $tokenGenerator;

    public function __construct(EntityManagerInterface $em, TokenGenerator $tokenGenerator)
    {
        $this->em = $em;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function saveUser(UserPasswordEncoderInterface $passwordEncoder, User $user): void
    {
        $user->setPassword($this->encodePassword($passwordEncoder, $user));
        $user->setToken($this->tokenGenerator->getToken());
        $user->setRoles(['ROLE_USER']);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function activateUser(User $user): void
    {
        $user->setIsActive(true);
        $user->setToken('');
        $this->em->persist($user);
        $this->em->flush();
    }

    private function encodePassword(UserPasswordEncoderInterface $passwordEncoder, User $user): string
    {
        return $passwordEncoder->encodePassword($user, $user->getPlainPassword());
    }

    public function changeStatus(int $id): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['id' => $id]);

        if ($user->getIsActive())
        {
            $user->setIsActive(false);
        }else
        {
            $user->setIsActive(true);
        }

        $this->em->persist($user);
        $this->em->flush();
    }
}