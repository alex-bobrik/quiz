<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\QuizCategory;
use App\Form\QuizCategoryType;
use App\Form\SimpleSearchType;
use App\Service\QuizService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class QuizCategoryController extends AbstractController
{
    /**
     * @Route("/admin/quiz-categories", name="admin_quiz-categories")
     */
    public function index(PaginatorInterface $paginator, Request $request, RouterInterface $router)
    {
        $categoriesRepository = $this->getDoctrine()->getRepository(QuizCategory::class);
        $categoriesQuery = $categoriesRepository->createQueryBuilder('c')
            ->getQuery();

        $q = $request->get('q');
        if ($q) {
            $categoriesQuery = $categoriesRepository->createQueryBuilder('c')
                ->select('c')
                ->where('c.name like :name')
                ->setParameter('name', '%'.$q.'%')
                ->getQuery();
        } else {
            $categoriesQuery = $categoriesRepository->createQueryBuilder('c')
                ->getQuery();
        }

        $searchForm = $this->createForm(SimpleSearchType::class, ['query' => $q]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $query = $searchForm->get('query')->getData();
            return new RedirectResponse($router->generate('admin_quiz-categories', ['q' => $query]));
        }

        $categories = $paginator->paginate(
            $categoriesQuery,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('quiz_category/index.html.twig', [
            'controller_name' => 'QuizCategoryController',
            'categories' => $categories,
            'searchForm' => $searchForm->createView(),
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

            if ($quizService->isCategoryExists($category->getName())) {
                $this->addFlash('danger', 'Такая категория уже существует');

                return $this->redirectToRoute('admin_quiz-categories_create');
            }

            $quizService->saveQuizCategory($category);
            $this->addFlash('success', 'Добавлена новая категория');

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
            $this->addFlash('success', 'Категория изменена');

            return $this->redirectToRoute('admin_quiz-categories');
        }

        return $this->render('quiz_category/edit.html.twig', [
            'controller_name' => 'QuizCategoryController',
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * @Route("/admin/quiz-categories/delete/{id}", name="admin_quiz-categories_delete", requirements={"id"="\d+"})
     * @param QuizService $quizService
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteQuizCategory(EntityManagerInterface $em, Request $request, int $id, RouterInterface $router)
    {
        $category = $this->getDoctrine()->getRepository(QuizCategory::class)->findOneBy(['id' => $id]);

        try {
            $em->remove($category);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlash('danger', 'Не удалось удалить категорию. Существуют викторины в данной категории.');
        }

        return $this->redirectToRoute('admin_quiz-categories');
    }
}
