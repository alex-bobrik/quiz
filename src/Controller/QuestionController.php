<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\User;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    /**
     * @Route("/admin/questions", name="admin_questions_show_all")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function showAllQuestions(EntityManagerInterface $em)
    {
        $questions = $em->getRepository(Question::class)->findAll();

        return $this->render('question/index.html.twig', [
            'controller_name' => 'QuestionController',
            'questions' => $questions,
        ]);
    }

    /**
     * @Route("/admin/questions/{id}", name="admin_question_show_info")
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Response
     */
    public function showQuestionInfo(EntityManagerInterface $em, int $id)
    {
        $question = $em->getRepository(Question::class)->find($id);
        $answers = $question->getAnswers();

        return $this->render('question/question_info.html.twig', [
            'controller_name' => 'QuestionController',
            'question' => $question,
            'answers' => $answers,
        ]);
    }

    /**
     * @Route("/question/update{id}", name="question_update", requirements={"id"="\d+"})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function updateQuestion(EntityManagerInterface $em, Request $request, int $id): Response
    {
        /** @var Question $question */
        $question = $em->getRepository(Question::class)->find($id);
        $question->setText('question');
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($form->getData());
            $question = $form->getData();
            dump($question);
            $em->persist($question);
            $em->flush();

            return $this->redirectToRoute('admin_question_show_info', array('id' => $question->getId()));
        }

        return $this->render('question/create.html.twig', [
            'controller_name' => 'QuestionController',
            'form' => $form->createView(),
        ]);
    }
}
