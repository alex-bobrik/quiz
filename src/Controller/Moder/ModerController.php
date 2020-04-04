<?php

namespace App\Controller\Moder;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizQuestion;
use App\Service\ModerService;
use App\Service\QuizService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModerController extends AbstractController
{
    /**
     * @Route("/moder/quizes", name="moder_quizes")
     */
    public function index()
    {
        $quizes = $this->getDoctrine()->getRepository(Quiz::class)->findBy(['isChecked' => false]);

        return $this->render('moder/index.html.twig', [
            'controller_name' => 'ModerController',
            'quizes' => $quizes,
        ]);
    }

    /**
     * @Route("/moder/quizes/{id}", name="moder_quizes_info", requirements={"id"="\d+"})
     * @param int $id
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function quizInfo(int $id, PaginatorInterface $paginator, Request $request)
    {
//        $questionsRepository = $this->getDoctrine()->getRepository(Question::class);
        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($id);

        $questions = $quiz->getQuestions();

        $questions = $paginator->paginate($questions, $request->query->getInt('page', 1), 1);

        return $this->render('moder/quiz_info.html.twig', [
            'controller_name' => 'ModerController',
            'quiz' => $quiz,
            'questions' => $questions,
        ]);
    }

    /**
     * @Route("/moder/quiz/deny/{id}",
     *     name="moder_quiz_deny",
     *     requirements={"id"="\d+"})
     * @param int $id
     * @param ModerService $moderService
     * @return Response
     */
    public function denyQuiz(int $id, ModerService $moderService): Response
    {
        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($id);

        $moderService->denyQuiz($quiz);
        return $this->redirectToRoute('moder_quizes');
    }

    /**
     * @Route("/moder/quiz/confirm/{id}",
     *     name="moder_quiz_confirm",
     *     requirements={"id"="\d+"})
     * @param int $id
     * @param ModerService $moderService
     * @return Response
     */
    public function confirmQuiz(int $id, ModerService $moderService): Response
    {
        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($id);

        $moderService->confirmQuiz($quiz);
        return $this->redirectToRoute('moder_quizes');
    }
}
