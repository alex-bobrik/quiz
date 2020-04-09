<?php

namespace App\Controller;

use App\Entity\QuizCategory;
use App\Form\QuizCategoryType;
use App\Service\QuizService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuizCategoryController extends AbstractController
{
    /**
     * @Route("/admin/quiz-categories", name="admin_quiz-categories")
     */
    public function index()
    {
        $categories = $this->getDoctrine()->getRepository(QuizCategory::class)->findAll();

        return $this->render('quiz_category/index.html.twig', [
            'controller_name' => 'QuizCategoryController',
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/admin/quiz-categories/create", name="admin_quiz-categories_create")
     * @param QuizService $quizService
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createQuizCategory(QuizService $quizService, Request $request)
    {
        $category = new QuizCategory();

        $form = $this->createForm(QuizCategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $quizService->saveQuizCategory($category);

            return $this->redirectToRoute('admin_quiz-categories');
        }

        return $this->render('quiz_category/create.html.twig', [
            'controller_name' => 'QuizCategoryController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/quiz-categories/edit/{id}", name="admin_quiz-categories_edit", requirements={"id"="\d+"})
     * @param QuizService $quizService
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editQuizCategory(QuizService $quizService, Request $request, int $id)
    {
        $category = $this->getDoctrine()->getRepository(QuizCategory::class)->find($id);

        $form = $this->createForm(QuizCategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();

            $quizService->saveQuizCategory($category);

            return $this->redirectToRoute('admin_quiz-categories');
        }

        return $this->render('quiz_category/edit.html.twig', [
            'controller_name' => 'QuizCategoryController',
            'form' => $form->createView(),
        ]);
    }
}
