<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\User;
use App\Form\Quiz\QuizType;
use App\Form\SimpleSearchType;
use App\Service\ModerService;
use App\Service\QuizService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class QuizController extends AbstractController
{
    /**
     * @Route("/admin/quizes", name="admin_quizes_show")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(PaginatorInterface $paginator, Request $request, RouterInterface $router): Response
    {
        $quizesRepository = $this->getDoctrine()->getRepository(Quiz::class);
        $quizesQuery = $quizesRepository->createQueryBuilder('q')
            ->getQuery();

        $q = $request->get('q');
        if ($q) {
            $quizesQuery = $quizesRepository->createQueryBuilder('q')
                ->select('q')
                ->where('q.name like :name')
                ->setParameter('name', '%'.$q.'%')
                ->getQuery();
        } else {
            $quizesQuery = $quizesRepository->createQueryBuilder('q')
                ->getQuery();
        }

        $searchForm = $this->createForm(SimpleSearchType::class, ['query' => $q]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $query = $searchForm->get('query')->getData();
            return new RedirectResponse($router->generate('admin_quizes_show', ['q' => $query]));
        }

        $quizes = $paginator->paginate(
            $quizesQuery,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('quiz/index.html.twig', [
            'controller_name' => 'QuizController',
            'quizes' => $quizes,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/quizes/{quizId}", name="admin_quizes_info", requirements={"quizId"="\d+"})
     * @param QuizService $quizService
     * @param int $quizId
     * @return Response
     */
    public function showInfo(QuizService $quizService, int $quizId): Response
    {
        $quiz = $quizService->findById($quizId);

        return $this->render('quiz/quiz_info.html.twig', [
            'questions' => $quiz->getQuestions(),
            'quiz' => $quiz,
        ]);
    }

    /**
     * @Route("/admin/quizes/create", name="admin_quizes_create")
     * @param QuizService $quizService
     * @param Request $request
     * @return Response
     */
    public function createQuiz(QuizService $quizService, Request $request): Response
    {
        $quiz = new Quiz();

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

        // Redirect to question creating if user dont have at least 1 question
        if (!$user->getQuestions()->count()) {
            $this->addFlash('info', 'Чтобы создать викторину, добавьте хотя бы один вопрос');
            return $this->redirectToRoute('admin_questions_create');
        }

        $quiz->setUser($user);

        $form = $this->createForm(QuizType::class, $quiz, ['user' => $user]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $quiz = $form->getData();
            $quizService->saveQuiz($quiz);
            return $this->redirectToRoute('admin_quizes_show');
        }

        return $this->render('quiz/create.html.twig', [
            'controller_name' => 'QuizController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/quizes/status/change/{quizId}",
     *     name="admin_quiz_change-status",
     *     requirements={"quizId"="\d+"})
     * @param QuizService $quizService
     * @param int $quizId
     * @return Response
     */
    public function changeQuizStatus(QuizService $quizService, int $quizId): Response
    {
        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($quizId);

        $quizService->changeStatus($quiz);
        if ($quiz->getIsActive()) {
            $this->addFlash('success', 'Изменен статус викторины \'' . $quiz->getName() . '\' на \'Активна\'');
        } else {
            $this->addFlash('success', 'Изменен статус викторины \'' . $quiz->getName() . '\' на \'Не активна\' ');
        }

        return $this->redirectToRoute('admin_quizes_show');
    }


}
