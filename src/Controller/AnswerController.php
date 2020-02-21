<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Answer;
use App\Form\AnswerType;
use App\Form\CreateAnswerType;
use App\Form\QuestionAnswerType;
use App\Service\AnswerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnswerController extends AbstractController
{
    private $answerService;

    public function __construct(AnswerService $answerService)
    {
        $this->answerService = $answerService;
    }

    /**
 * @Route("/admin/answers", name="admin_answers_show")
 */
    public function showAnswers(): Response
    {
        $answers = $this->answerService->getAnswers();

        return $this->render('answer/index.html.twig', [
            'controller_name' => 'AnswerController',
            'answers' => $answers,
        ]);
    }

    /**
     * @Route("/admin/answers/create", name="admin_answers_create")
     * @param Request $request
     * @return Response
     */
    public function createAnswer(Request $request): Response
    {
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $answer = $form->getData();
            $this->answerService->addAnswer($answer);
            return $this->redirectToRoute('admin_answers_show');
        }

        return $this->render('answer/create.html.twig', [
            'controller_name' => 'AnswerController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/answers/edit/{id}", name="admin_answers_edit")
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Exception
     */
    public function editAnswer(Request $request, int $id): Response
    {
        $answer = $this->answerService->getAnswerById($id);

        if (!$answer)
        {
            throw new \Exception('No answers in DB with id' . $id);
        }

        $form = $this->createForm(AnswerType::class, $answer);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $answer = $form->getData();
            $this->answerService->editAnswer($answer);
            return $this->redirectToRoute('admin_answers_show');
        }

        return $this->render('answer/edit.html.twig', [
            'controller_name' => 'AnswerController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/answers/delete/{id}", name="admin_answers_delete")
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function deleteAnswer(Request $request, int $id): Response
    {
        $answer = $this->answerService->getAnswerById($id);
        $this->answerService->deleteAnswer($answer);

        return $this->redirectToRoute('admin_answers_show');
    }

}
