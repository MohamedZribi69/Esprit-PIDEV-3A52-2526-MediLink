<?php

namespace App\Controller\Admin;

use App\Entity\Medicament;
use App\Form\Admin\MedicamentType;
use App\Repository\MedicamentRepository;
use App\Service\GeminiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/medicaments')]
final class MedicamentAdminController extends AbstractController
{
    #[Route('', name: 'admin_medicaments_index', methods: ['GET'])]
    public function index(Request $request, MedicamentRepository $repo): Response
    {
        $q = (string) $request->query->get('q', '');
        $qb = $repo->createQueryBuilder('m')->orderBy('m.nom', 'ASC');
        if ($q !== '') {
            $qb->andWhere('LOWER(m.nom) LIKE :q OR LOWER(m.description) LIKE :q')
               ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }
        $medicaments = $qb->getQuery()->getResult();

        return $this->render('admin/medicament/index.html.twig', [
            'medicaments' => $medicaments,
            'q' => $q,
        ]);
    }

    #[Route('/new', name: 'admin_medicaments_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $medicament = new Medicament();
        $form = $this->createForm(MedicamentType::class, $medicament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($medicament);
                $em->flush();
                $this->addFlash('success', 'Médicament ajouté.');
                return $this->redirectToRoute('admin_medicaments_index');
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur base de données : ' . $e->getMessage());
            }
        }

        return $this->render('admin/medicament/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_medicaments_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Medicament $medicament, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MedicamentType::class, $medicament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Médicament modifié.');
            return $this->redirectToRoute('admin_medicaments_index');
        }

        return $this->render('admin/medicament/edit.html.twig', [
            'form' => $form->createView(),
            'medicament' => $medicament,
        ]);
    }

    #[Route('/{id}', name: 'admin_medicaments_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Medicament $medicament, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_medicament_' . $medicament->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $em->remove($medicament);
        $em->flush();
        $this->addFlash('success', 'Médicament supprimé.');
        return $this->redirectToRoute('admin_medicaments_index');
    }

    #[Route('/api/generer-description', name: 'admin_medicaments_generer_description', methods: ['POST'])]
    public function genererDescription(Request $request, GeminiService $geminiService): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $nom = $data['nom'] ?? '';

            if (empty($nom)) {
                return $this->json(['error' => 'Le nom du médicament est requis.'], 400);
            }

            $result = $geminiService->genererDescriptionMedicament($nom);

            if (!$result['success']) {
                return $this->json(['error' => $result['error'] ?? 'Erreur inconnue'], 500);
            }

            return $this->json(['description' => $result['description']]);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }
}
