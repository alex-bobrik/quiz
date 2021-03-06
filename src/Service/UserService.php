<?php declare(strict_types=1);

namespace App\Service;


use App\Entity\Game;
use App\Entity\Rating;
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
        $user->setNickname($this->generateNickname());

        $this->em->persist($user);
        $this->em->flush();
    }

    public function saveUserAfterRecovery(UserPasswordEncoderInterface $passwordEncoder, User $user): void
    {
        $user->setPassword($this->encodePassword($passwordEncoder, $user));
        $user->setToken('');

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

    private function generateNickname(): string
    {
        $numbers = hexdec(rand(10, 99) . uniqid());

        return 'user' . $numbers;
    }

    public function changeStatus(User $user): void
    {
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

    public function getUserRating(User $user): ?string
    {
        $result = $this->em->getRepository(Rating::class)
            ->createQueryBuilder('r')
            ->select('avg(r.stars)')
            ->where('r.quiz in (:quizes)')
            ->setParameter('quizes', $user->getQuizzes())
            ->getQuery()
            ->getResult();

        return $result[0][1];
    }

    public function getUnfinishedGames(User $user)
    {
        $unfinishedGames = $this->em->getRepository(Game::class)->findBy(['user' => $user, 'end_date' => null]);

        return $unfinishedGames;
    }

    public function getFinishedGames(User $user): ?array
    {
        $userGames = $user->getGames();

        $userCompleteQuizes = array();

        foreach ($userGames as $userGame) {
            if ($userGame->getGameIsOver()) {
                $userCompleteQuizes[] = $userGame->getQuiz();
            }
        }

        return $userCompleteQuizes;
    }
}