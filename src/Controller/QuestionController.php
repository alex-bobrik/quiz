<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Form\Question\QuestionType;
use App\Service\QuestionService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
//    /**
//     * @Route("/admin/questions", name="admin_questions_show")
//     * @param PaginatorInterface $paginator
//     * @param Request $request
//     * @return Response
//     */
//    public function showAllQuestions(PaginatorInterface $paginator, Request $request): Response
//    {
//        $questionsRepository = $this->getDoctrine()->getRepository(Question::class);
//
//        $questionsQuery = $questionsRepository->createQueryBuilder('q')
//            ->getQuery();
//
//        $questions = $paginator->paginate(
//            $questionsQuery,
//            $request->query->getInt('page', 1),
//            7
//        );
//
//        return $this->render('question/index.html.twig', [
//            'controller_name' => 'QuestionController',
//            'questions' => $questions,
//        ]);
//    }

    /**
     * @Route("/admin/questions/{id}", name="admin_question_info", requirements={"id"="\d+"})
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Response
     */
    public function showQuestionInfo(EntityManagerInterface $em, int $id): Response
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
     * @Route("/admin/questions/create", name="admin_questions_create")
     *
     * @param QuestionService $questionService
     * @param Request $request
     * @return Response
     */
    public function createQuestion(QuestionService $questionService, Request $request): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData();
            $questionService->saveQuestion($question);
            return $this->redirectToRoute('admin_question_info', array('id' => $question->getId()));
        }

        return $this->render('question/create.html.twig', [
            'controller_name' => 'QuestionController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/questions/update/{id}", name="admin_questions_update", requirements={"id"="\d+"})
     *
     * @param QuestionService $questionService
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function updateQuestion(QuestionService $questionService,
                                   EntityManagerInterface $em,
                                   Request $request,
                                   int $id
    ): Response
    {
        $question = $em->getRepository(Question::class)->find($id);
        $form = $this->createForm(QuestionType::class, $question);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData();
            $questionService->saveQuestion($question);
            return $this->redirectToRoute('admin_question_info', ['id' => $question->getId()]);
        }

        return $this->render('question/update.html.twig', [
            'controller_name' => 'QuestionController',
            'form' => $form->createView(),
        ]);
    }
}
