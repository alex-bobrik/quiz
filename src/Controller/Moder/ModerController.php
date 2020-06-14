<?php

namespace App\Controller\Moder;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizCategory;
use App\Entity\QuizQuestion;
use App\Entity\User;
use App\Entity\Violation;
use App\Form\DenyQuizType;
use App\Form\SimpleSearchType;
use App\Service\ModerService;
use App\Service\QuizService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class ModerController extends AbstractController
{
    /**
     * @Route("/moder/quizes", name="moder_quizes")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param RouterInterface $router
     * @return RedirectResponse|Response
     */
    public function index(Request $request, PaginatorInterface $paginator, RouterInterface $router)
    {
        $quizesRepository = $this->getDoctrine()->getRepository(Quiz::class);

        $q = $request->get('q');
        if ($q) {
            $quizesQuery = $quizesRepository->createQueryBuilder('q')
                ->select('q')
                ->where('q.name like :name')
                ->andWhere('q.isChecked = :check')
                ->setParameter('check', false)
                ->setParameter('name', '%'.$q.'%')
                ->getQuery();
        } else {
            $quizesQuery = $quizesRepository->createQueryBuilder('q')
                ->select('q')
                ->where('q.isChecked = :check')
                ->setParameter('check', false)
                ->getQuery();
        }

        $searchForm = $this->createForm(SimpleSearchType::class, ['query' => $q]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $query = $searchForm->get('query')->getData();
            return new RedirectResponse($router->generate('moder_quizes', ['q' => $query]));
        }

        $quizes = $paginator->paginate(
            $quizesQuery,
            $request->query->getInt('page', 1),
            7
        );


        return $this->render('moder/index.html.twig', [
            'controller_name' => 'ModerController',
            'quizes' => $quizes,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    /**
     * @Route("/moder/quizes/{id}", name="moder_quizes_info", requirements={"id"="\d+"})
     * @param int $id
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param ModerService $moderService
     * @return Response
     */
    public function quizInfo(PaginatorInterface $paginator, Request $request, ModerService $moderService, int $id)
    {
        /** @var Quiz $quiz */
        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($id);

        if ($quiz->getIsChecked()) {
            $this->addFlash('danger', 'Викторина уже проверена');

            return $this->redirectToRoute('moder_quizes');
        }

        // Pagination for questions
        $questions = $quiz->getQuestions();
        $questions = $paginator->paginate($questions, $request->query->getInt('page', 1), 1);

        // Deny form
        $form = $this->createForm(DenyQuizType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $violation = $form->get('violation')->getData();
            $moderService->denyQuiz($quiz, $violation);
            $this->addFlash('success', 'Викторина \''. $quiz->getName() . '\' отклонена');

            return $this->redirectToRoute('moder_quizes');
        }

        return $this->render('moder/quiz_info.html.twig', [
            'controller_name' => 'ModerController',
            'quiz' => $quiz,
            'questions' => $questions,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/moder/quiz/confirm/{id}",
     *     name="moder_quiz_confirm",
     *     requirements={"id"="\d+"})
     * @param int $id
     * @param ModerService $moderService
     * @return Response
     */
    public function confirmQuiz(ModerService $moderService, int $id): Response
    {
        /** @var Quiz $quiz */
        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($id);

        /** @var User $whoConfirm */
        $whoConfirm = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

        $moderService->confirmQuiz($quiz, $whoConfirm);
        $this->addFlash('success', 'Викторина \'' . $quiz->getName() . '\' подтверждена');
        return $this->redirectToRoute('moder_quizes');
    }

    /**
     * @Route("/moder/rules", name="moder_rules")
     * @return Response
     */
    public function rules(): Response
    {
        $violations = $this->getDoctrine()->getRepository(Violation::class)->findAll();

        return $this->render('moder/rules.html.twig', [
            'violations' => $violations,
        ]);
    }
}
