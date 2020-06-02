<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Form\DatepickerType;
use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class StatsController extends AbstractController
{
    /**
     * @Route("/admin/quizes/stats", name="admin_quizes_stats")
     */
    public function index(EntityManagerInterface $em, StatsService $statsService, Request $request, RouterInterface $router)
    {

        // Quizes stats
        $quizesHeader = array();
        $quizesHeader[] = 'Дата';
        $quizesHeader[] = 'Количество';

        $begin = new \DateTime($request->query->get('begin'));
        $end = new \DateTime($request->query->get('end'));
        $statsService->getQuizesParamsStats($begin, $end);

        $quizesStats = $statsService->getQuizesAmountStats($begin, $end);

        if (!$quizesStats) {
            $obj = array();
            $obj['obj'] = 0;
            $obj['amount'] = 0;

            $quizesStats = array();
            $quizesStats[] = $obj;
        }

        $pickerQuizes = $this->createForm(DatepickerType::class, ['begin' => $begin, 'end' => $end]);
        $pickerQuizes->handleRequest($request);
        if ($pickerQuizes->isSubmitted()) {

            $begin = $pickerQuizes->get('begin')->getData();
            $end = $pickerQuizes->get('end')->getData();

            return new RedirectResponse($router->generate('admin_quizes_stats', [
                'begin' => $begin->format('Y/m/d'),
                'end' => $end->format('Y/m/d')
            ]));
        }

        $quizesChart = $statsService->getChart($quizesStats, '', $quizesHeader);

        $stats = $statsService->getQuizesParamsStats($begin, $end);

        return $this->render('stats/index.html.twig', [
            'controller_name' => 'StatsController',
            'quizesChart' => $quizesChart,
            'stats' => $stats,
            'quizPicker' => $pickerQuizes->createView(),
        ]);
    }
}
