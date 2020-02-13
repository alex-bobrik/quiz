<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Service\QuizService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    /**
     * @Route("/admin/quizes", name="admin_quizes_show_all")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function index(EntityManagerInterface $em)
    {
        $quizes = $em->getRepository(Quiz::class)->findAll();

        return $this->render('quiz/index.html.twig', [
            'controller_name' => 'QuizController',
            'quizes' => $quizes,
        ]);
    }

    /**
     * @Route("/admin/quizes/create", name="admin_quiz_create", requirements={"id"="\d+"})
     *
     * @param QuizService $quizService
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return Response
     */
    public function createQuiz(QuizService $quizService, EntityManagerInterface $em, Request $request): Response
    {
        /** @var Quiz $quiz */
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $quiz = $form->getData();
            $quizService->createQuiz($quiz);
        }

        return $this->render('quiz/create.html.twig', [
            'controller_name' => 'QuizController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/quizes/update{id}", name="admin_quiz_update", requirements={"id"="\d+"})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function updateQuiz(EntityManagerInterface $em, Request $request, int $id): Response
    {
        /** @var Quiz $quiz */
        $quiz = $em->getRepository(Quiz::class)->find($id);
        $quiz->setName('first quiz');
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $quiz = $form->getData();
            $em->persist($quiz);
            $em->flush();
        }

        return $this->render('quiz/update.html.twig', [
            'controller_name' => 'QuizController',
            'form' => $form->createView(),
        ]);
    }
}
