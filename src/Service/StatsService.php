<?php


namespace App\Service;


use App\Entity\Quiz;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ColumnChart;
use Doctrine\ORM\EntityManagerInterface;

class StatsService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getChart(array $data, string $title, array $header): ColumnChart
    {
        $chart = new ColumnChart();

        $chartData = array();
        $chartData[] = $header;

        foreach ($data as $el) {
            $array = array();
            $array[] = $el['obj'];
            $array[] = (int)$el['amount'];
            $chartData[] = $array;
        }

        $chart->getData()->setArrayToDataTable(
            $chartData
        );

        $chart->getOptions()->setTitle($title);
        $chart->getOptions()->setHeight(500);
        $chart->getOptions()->getTitleTextStyle()->setBold(false);
        $chart->getOptions()->setColors(['#00b742']);
        $chart->getOptions()->getTitleTextStyle()->setColor('#6184ff');
        $chart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $chart->getOptions()->getTitleTextStyle()->setFontSize(20);
        $chart->getOptions()->getVAxis()->setFormat('#');
        $chart->getOptions()->getVAxis()->setMinValue(1);
        $chart->getOptions()->getHAxis()->setMinValue(1);

        return $chart;

    }

    // Returns dateCreated(d m Y) - count of quizes for a selected date
    public function getQuizesAmountStats(\DateTime $begin, \DateTime $end): ?array
    {

        $result = $this->em->getRepository(Quiz::class)
            ->createQueryBuilder('q')
            ->select('date_format(q.created, \'%d %M %Y\') as obj', 'count(q.created) as amount')
            ->where('q.created between :begin and :end')
            ->groupBy('obj')
            ->setParameter('begin', $begin)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        return $result;
    }

    // Returns stats by all quizes for a selected period
    public function getQuizesParamsStats(\DateTime $begin, \DateTime $end): ?array
    {
        $quizes = $this->em->getRepository(Quiz::class)
            ->createQueryBuilder('q')
            ->select('q')
            ->where('q.created between :begin and :end')
            ->setParameter('begin', $begin)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        if (!$quizes) {
            $result = array();

            $result['all']               = 0;
            $result['limited']           = 0;
            $result['unlimited']         = 0;
            $result['average_questions'] = 0;

            return $result;
        }

        $timeLimitedQuizes = 0;
        foreach ($quizes as $quiz) {
            if ($quiz->getIsTimeLimited()) {
                $timeLimitedQuizes++;
            }
        }

        $allQuestions = 0;
        foreach ($quizes as $quiz) {
            $allQuestions += $quiz->getQuestions()->count();
        }

        $result = array();
        $result['all']               = count($quizes);                                 // amount of all quizes
        $result['limited']           = $timeLimitedQuizes;                             // of limited by time quizes
        $result['unlimited']         = $result['all'] - $timeLimitedQuizes;            // unlimited by time quizes
        $result['average_questions'] = (int)round($allQuestions / count($quizes)); // average questions amount per quiz

        return $result;
    }

}