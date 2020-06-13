<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_index")
     */
    public function index(): Response
    {

//        $quizes = $this->getDoctrine()->getRepository(Question::class)->findAll();
//        foreach ($quizes as $quiz) {
//            $this->getDoctrine()->getManager()->remove($quiz);
//        }

        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('admin_users_show');
//        return $this->render('admin/index.html.twig', [
//            'controller_name' => 'AdminController',
//        ]);
    }
}
