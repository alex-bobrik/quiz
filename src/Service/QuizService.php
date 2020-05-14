<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Quiz;
use App\Entity\QuizCategory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;

class QuizService
{
    private $em;

    private $security;

    private $gameService;

    private $fileUploader;

    private $paginator;

    // Milliseconds (1s = 60000ms)
    const MILLISECONDS = 60000;

    // Average read speed is 1500syms/min
    const READ_SPEED = 1500;

    // Min time limit is 15000ms (15s)
    const MIN_TIME_LIMIT = 15000;

    public function __construct
    (
        EntityManagerInterface $em,
        Security $security,
        GameService $gameService,
        FileUploader $fileUploader,
        PaginatorInterface $paginator
    )
    {
        $this->em = $em;
        $this->security = $security;
        $this->gameService = $gameService;
        $this->fileUploader = $fileUploader;
        $this->paginator = $paginator;
    }

    public function findById(int $id): ?Quiz
    {
        return $this->em->getRepository(Quiz::class)->find($id);
    }

    public function saveQuiz(Quiz $quiz): void
    {
        $file = new UploadedFile($quiz->getImage(), 'fileName');

        $quiz->setImage($this->fileUploader->uploadQuizImage($file, $quiz));
        $currentUser = $this->security->getUser();

        $user = $this->em->getRepository(User::class)->find($currentUser);

        $date = new \DateTime('now');
        $quiz->setCreated($date);

        // If admin or moder creating quiz, quiz activating instantly
        if ($user->getRoles()[0] == 'ROLE_USER') {
            $quiz->setIsActive(false);
            $quiz->setIsChecked(false);
        } else {
            $quiz->setIsActive(true);
            $quiz->setIsChecked(true);
        }

        $quiz->setUser($user);

        $this->em->persist($quiz);
        $this->em->flush();
    }

    public function changeStatus(Quiz $quiz): void
    {
        if ($quiz->getIsActive()) {
            $this->endAllGames($quiz);
            $quiz->setIsActive(false);
        } else {
            $quiz->setIsActive(true);
        }

        $this->em->persist($quiz);
        $this->em->flush();
    }

    private function endAllGames(Quiz $quiz)
    {
        $games = $quiz->getGames();

        foreach ($games as $game) {
            $this->gameService->endGame($game);
        }
    }

    public function saveQuizCategory(QuizCategory $quizCategory)
    {
        $this->em->persist($quizCategory);
        $this->em->flush();
    }

    public function getTimeLimit(Quiz $quiz): int
    {
        $quizQuestions = $quiz->getQuestions();

        $characters = 0;
        foreach ($quizQuestions as $quizQuestion) {
            $characters += strlen($quizQuestion->getQuestion()->getText());
            $answers = $quizQuestion->getQuestion()->getAnswers();
            foreach ($answers as $answer) {
                $characters += strlen($answer->getText());
            }
        }

        $timeLimit = ($characters * self::MILLISECONDS) / self::READ_SPEED;

        if ($timeLimit < self::MIN_TIME_LIMIT) {
            $timeLimit = self::MIN_TIME_LIMIT;
        }

        return $timeLimit;
    }

    // Return Query object for paginator
    public function search
    (
        string $query = null,
        array $categories = null,
        array $limit = null,
        bool $isActiveQuiz = true
    ): Query
    {
        $quizesRepository = $this->em->getRepository(Quiz::class);

        // Search all quizes
        if (!$query && !$categories) {
            $quizesQuery = $quizesRepository->createQueryBuilder('q');
        }
        // Search only by query
        elseif ($query && !$categories) {
            $quizesQuery = $quizesRepository->createQueryBuilder('q')
                ->select('q')
                ->where('q.name like :name')
                ->setParameter('name', '%'.$query.'%');
        }
        // Search by query and categories
        else {
            $categories = $this->em->getRepository(QuizCategory::class)->findBy(['id' => $categories]);

            $quizesQuery = $quizesRepository->createQueryBuilder('q')
                ->select('q')
                ->where('q.name like :name')
                ->andWhere('q.quizCategory in (:categories)')
                ->setParameter('name', '%'.$query.'%')
                ->setParameter('categories', $categories);
        }

        // Search by isActive option (true/false)
        $quizesQuery->andWhere('q.isActive = :active')->setParameter('active', $isActiveQuiz);

        // Search by time limit option
        if (!$limit || count($limit) == 2) {
            return $quizesQuery->getQuery();
        } elseif (in_array('limit', $limit)) {
            return $quizesQuery->andWhere('q.isTimeLimited = :limit')->setParameter('limit', true)->getQuery();
        } elseif (in_array('no-limit', $limit)) {
            return $quizesQuery->andWhere('q.isTimeLimited = :limit')->setParameter('limit', false)->getQuery();
        }

        return $quizesQuery->getQuery();
    }

    public function isCategoryExists(string $category): bool
    {
        $quizCategory = $this->em
            ->getRepository(QuizCategory::class)
            ->findOneBy(['name' => $category]);

        if ($quizCategory) {
            return true;
        }

        return false;
    }

}