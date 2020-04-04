<?php declare(strict_types=1);


namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AdminService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function appointAsModer(User $user): void
    {
        $user->setRoles(['ROLE_MODER']);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function rejectModer(User $user): void
    {
        $user->setRoles(['ROLE_USER']);
        $this->em->persist($user);
        $this->em->flush();
    }
}