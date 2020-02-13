<?php declare(strict_types=1);

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
     * @Route("/admin/quizes/create", name="admin_quizes_create")
     * @param QuizService $quizService
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return Response
     */
    public function createQuiz(QuizService $quizService, EntityManagerInterface $em, Request $request): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $quiz = $form->getData();
            $quizService->saveQuiz($quiz);
            return $this->redirectToRoute('admin_quizes_show_all');
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
    public function updateQuiz(QuizService $quizService, EntityManagerInterface $em, Request $request, int $id): Response
    {
        /** @var Quiz $quiz */
        $quiz = $em->getRepository(Quiz::class)->find($id);
        $form = $this->createForm(QuizType::class, $quiz);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $quiz = $form->getData();
            $quizService->saveQuiz($quiz);
            return $this->redirectToRoute('admin_quizes_show_all');
        }

        return $this->render('quiz/update.html.twig', [
            'controller_name' => 'QuizController',
            'form' => $form->createView(),
        ]);
    }
}
