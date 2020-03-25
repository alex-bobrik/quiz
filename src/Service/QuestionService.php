<?php declare(strict_types=1);


namespace App\Service;


use App\Entity\Question;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class QuestionService
{
    private $em;

    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public function saveQuestion(Question $question): void
    {
        $currentUser = $this->security->getUser();

        $user = $this->em->getRepository(User::class)->find($currentUser);

        $question->setUser($user);

        $this->em->persist($question);
        $this->em->flush();
    }
}