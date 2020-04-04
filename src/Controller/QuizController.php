<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\User;
use App\Form\Quiz\QuizType;
use App\Service\ModerService;
use App\Service\QuizService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    /**
     * @Route("/admin/quizes", name="admin_quizes_show")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $quizesRepository = $this->getDoctrine()->getRepository(Quiz::class);

        $quizesQuery = $quizesRepository->createQueryBuilder('q')
            ->getQuery();

        $quizes = $paginator->paginate(
            $quizesQuery,
            $request->query->getInt('page', 1),
            7
        );

        return $this->render('quiz/index.html.twig', [
            'controller_name' => 'QuizController',
            'quizes' => $quizes,
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
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

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
     * @Route("/admin/quizes/update/{id}", name="admin_quizes_update", requirements={"id"="\d+"})
     *
     * @param QuizService $quizService
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function updateQuiz(QuizService $quizService,
                               EntityManagerInterface $em,
                               Request $request,
                               int $id
    ): Response
    {
        $quiz = $em->getRepository(Quiz::class)->find($id);

        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
        $form = $this->createForm(QuizType::class, $quiz, ['user' => $user]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $quiz = $form->getData();
            $quizService->saveQuiz($quiz);
            return $this->redirectToRoute('admin_quizes_show');
        }

        return $this->render('quiz/update.html.twig', [
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
        $quizService->changeStatus($quizId);
        return $this->redirectToRoute('admin_quizes_show');
    }


}
