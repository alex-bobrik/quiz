<?php

namespace App\Controller;

use App\Entity\QuizCategory;
use App\Entity\Violation;
use App\Entity\ViolationAct;
use App\Form\QuizCategoryType;
use App\Form\SimpleSearchType;
use App\Form\ViolationType;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class ViolationController extends AbstractController
{
    /**
     * @Route("/admin/violations", name="admin_violations")
     * @param Request $request
     * @param RouterInterface $router
     * @param PaginatorInterface $paginator
     * @return RedirectResponse|Response
     */
    public function index(Request $request, RouterInterface $router, PaginatorInterface $paginator)
    {
        $violationsRepository = $this->getDoctrine()->getRepository(Violation::class);
        $violationsQuery = $violationsRepository->createQueryBuilder('v')
            ->getQuery();

        $q = $request->get('q');
        if ($q) {
            $violationsQuery = $violationsRepository->createQueryBuilder('v')
                ->select('v')
                ->where('v.name like :name')
                ->setParameter('name', '%'.$q.'%')
                ->getQuery();
        } else {
            $violationsQuery = $violationsRepository->createQueryBuilder('v')
                ->getQuery();
        }

        $searchForm = $this->createForm(SimpleSearchType::class, ['query' => $q]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $query = $searchForm->get('query')->getData();
            return new RedirectResponse($router->generate('admin_violations', ['q' => $query]));
        }

        $violations = $paginator->paginate(
            $violationsQuery,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('violation/index.html.twig', [
            'controller_name' => 'ViolationController',
            'violations' => $violations,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    /**
     * @Route("/admin/violations/create", name="admin_violations_create")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function createViolation(Request $request, EntityManagerInterface $em)
    {
        $violation = new Violation();

        $form = $this->createForm(ViolationType::class, $violation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $violation = $form->getData();

            $em->persist($violation);
            $em->flush();
            $this->addFlash('success', 'Добавлен новый пункт нарушений');

            return $this->redirectToRoute('admin_violations');
        }

        return $this->render('violation/create.html.twig', [
            'controller_name' => 'ViolationController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/violations/edit/{id}", name="admin_violations_edit", requirements={"id"="\d+"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function editViolation(Request $request, EntityManagerInterface $em, int $id)
    {
        $violation = $this->getDoctrine()->getRepository(Violation::class)->find($id);


        $form = $this->createForm(ViolationType::class, $violation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $violation = $form->getData();

            $em->flush();

            $this->addFlash('success', 'Изменен пункт нарушений');

            return $this->redirectToRoute('admin_violations');
        }

        return $this->render('violation/edit.html.twig', [
            'controller_name' => 'ViolationController',
            'form' => $form->createView(),
            'violation' => $violation,
        ]);
    }

    /**
     * @Route("/admin/violations/delete/{id}", name="admin_violations_delete", requirements={"id"="\d+"})
     * @param EntityManagerInterface $em
     * @param int $id
     * @return RedirectResponse
     */
    public function deleteViolation(EntityManagerInterface $em, int $id)
    {
        $violation = $this->getDoctrine()->getRepository(Violation::class)->find($id);

        try {
            $em->remove($violation);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlash('danger', 'Не удалось удалить пункт нарушений. Существуют акты нарушений с данным пунктом');
        }

        return $this->redirectToRoute('admin_violations');
    }

    /**
     * @Route("/admin/violations/acts", name="admin_violations_acts")
     * @param Request $request
     * @param RouterInterface $router
     * @param PaginatorInterface $paginator
     * @return RedirectResponse|Response
     */
    public function violationActs(Request $request, RouterInterface $router, PaginatorInterface $paginator)
    {
        $actsRepo = $this->getDoctrine()->getRepository(ViolationAct::class);
        $actsQuery = $actsRepo->createQueryBuilder('a')
            ->getQuery();

        $q = $request->get('q');
        if ($q) {
            $actsQuery = $actsRepo->createQueryBuilder('a')
                ->select('a')
                ->join('a.violation', 'v')
                ->where('v.name like :name')
                ->setParameter('name', '%'.$q.'%')
                ->getQuery();
        } else {
            $actsQuery = $actsRepo->createQueryBuilder('v')
                ->getQuery();
        }

        $searchForm = $this->createForm(SimpleSearchType::class, ['query' => $q]);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted()) {
            $query = $searchForm->get('query')->getData();
            return new RedirectResponse($router->generate('admin_violations_acts', ['q' => $query]));
        }

        $acts = $paginator->paginate(
            $actsQuery,
            $request->query->getInt('page', 1),
            10
        );


        return $this->render('violation/acts.html.twig', [
            'acts' => $acts,
            'searchForm' => $searchForm->createView(),
        ]);
    }
}
