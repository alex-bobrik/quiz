<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Form\Question\QuestionType;
use App\Form\SimpleSearchType;
use App\Service\QuestionService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class QuestionController extends AbstractController
{
    /**
     * @Route("/questions", name="questions")
     * @return Response
     */
    public function userQuestions(PaginatorInterface $paginator, Request $request, RouterInterface $router): Response
    {
        $questionsRepository = $this->getDoctrine()->getRepository(Question::class);

        $q = $request->get('q');

        if (!$q) {
            $questionsQuery = $questionsRepository->createQueryBuilder('q')
                ->select('q')
                ->where('q.user = :user')
                ->setParameter('user', $this->getUser())
                ->getQuery();
        } else {
            $questionsQuery = $questionsRepository->createQueryBuilder('q')
                ->select('q')
                ->where('q.text like :text')
                ->andWhere('q.user = :user')
                ->setParameter('user', $this->getUser())
                ->setParameter('text', '%'.$q.'%')
                ->getQuery();
        }

        $searchForm = $this->createForm(SimpleSearchType::class, ['query' => $q]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $query = $searchForm->get('query')->getData();

            return new RedirectResponse($router->generate('questions', ['q' => $query]));
        }

        $questions = $paginator->paginate(
            $questionsQuery,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('question/index.html.twig', [
            'controller_name' => 'QuestionController',
            'questions' => $questions,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    /**
     * @Route("/questions/{id}", name="question_info", requirements={"id"="\d+"})
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Response
     */
    public function showQuestionInfo(EntityManagerInterface $em, int $id): Response
    {
        $question = $em->getRepository(Question::class)->find($id);

        if ($question->getUser() != $this->getUser()) {
            throw $this->createNotFoundException();
        }

        $answers = $question->getAnswers();

        return $this->render('question/question_info.html.twig', [
            'controller_name' => 'QuestionController',
            'question' => $question,
            'answers' => $answers,
        ]);
    }

    /**
     * @Route("/questions/create", name="questions_create")
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
            return $this->redirectToRoute('question_info', array('id' => $question->getId()));
        }

        return $this->render('question/create.html.twig', [
            'controller_name' => 'QuestionController',
            'form' => $form->createView(),
        ]);
    }
}
