<?php

declare(strict_types=1);

namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

    public function getIdByUsername(string $username): ?int
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);

        if ($user)
        {
            return $user->getId();
        }

        return null;
    }
}