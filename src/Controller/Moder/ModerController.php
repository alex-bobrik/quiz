<?php

namespace App\Controller\Moder;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizQuestion;
use App\Form\DenyQuizType;
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
    public function quizInfo(int $id, PaginatorInterface $paginator, Request $request, ModerService $moderService)
    {
        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($id);

        // pagination
        $questions = $quiz->getQuestions();
        $questions = $paginator->paginate($questions, $request->query->getInt('page', 1), 1);

        // deny form
        $form = $this->createForm(DenyQuizType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $violation = $form->get('violation')->getData();
            $moderService->denyQuiz($quiz, $violation);

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
    public function confirmQuiz(int $id, ModerService $moderService): Response
    {
        $quiz = $this->getDoctrine()->getRepository(Quiz::class)->find($id);

        $moderService->confirmQuiz($quiz);
        return $this->redirectToRoute('moder_quizes');
    }
}
