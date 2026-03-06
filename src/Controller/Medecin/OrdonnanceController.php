<?php

namespace App\Controller\Medecin;

use App\Entity\Ordonnance;
use App\Entity\User;
use App\Form\Medecin\OrdonnanceType;
use App\Repository\MedicamentRepository;
use App\Repository\OrdonnanceRepository;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/medecin')]
#[IsGranted('ROLE_MEDECIN')]
final class OrdonnanceController extends AbstractController
{
    #[Route('/ordonnances', name: 'medecin_ordonnances_index', methods: ['GET'])]
    public function index(OrdonnanceRepository $repo): Response
    {
        $medecin = $this->getUser();
        if (!$medecin instanceof User) {
            throw $this->createAccessDeniedException();
        }
        $ordonnances = $repo->findByMedecin($medecin);
        return $this->render('medecin/ordonnance/index.html.twig', [
            'ordonnances' => $ordonnances,
        ]);
    }

    #[Route('/ordonnances/new', name: 'medecin_ordonnances_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo,
        MedicamentRepository $medicamentRepo,
        EmailService $emailService
    ): Response
    {
        $medecin = $this->getUser();
        if (!$medecin instanceof User) {
            throw $this->createAccessDeniedException();
        }
        $ordonnance = new Ordonnance();
        $ordonnance->setMedecin($medecin);

        $form = $this->createForm(OrdonnanceType::class, $ordonnance, [
            'patients' => $userRepo->findPatients(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ne pas persister de ligne sans médicament valide (évite ForeignKeyConstraintViolationException)
            foreach ($ordonnance->getOrdonnanceMedicaments()->toArray() as $om) {
                $med = $om->getMedicament();
                if ($med === null || $med->getId() === null || $medicamentRepo->find($med->getId()) === null) {
                    $ordonnance->removeOrdonnanceMedicament($om);
                }
            }
            $em->persist($ordonnance);
            $em->flush();

            // Construire une liste HTML des médicaments pour l'e-mail
            $medicamentsHtml = '<ul>';
            foreach ($ordonnance->getOrdonnanceMedicaments() as $om) {
                $m = $om->getMedicament();
                $nom = $m ? $m->getNom() : '';
                $medicamentsHtml .= sprintf(
                    '<li><strong>%s</strong> (Quantité : %d)</li>',
                    htmlspecialchars($nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    $om->getQuantite()
                );
            }
            $medicamentsHtml .= '</ul>';

            $doctor = $ordonnance->getMedecin();
            $patient = $ordonnance->getPatient();
            $doctorName = $doctor?->getFullName() ?? $doctor?->getEmail() ?? 'Inconnu';
            $patientName = $patient?->getFullName() ?? $patient?->getEmail() ?? 'Inconnu';

            if (!$emailService->sendOrdonnanceNotification($doctorName, $patientName, $medicamentsHtml)) {
                $this->addFlash('warning', 'Ordonnance créée, mais la notification par e-mail à l\'administrateur n\'a pas pu être envoyée.');
            }

            $this->addFlash('success', 'Ordonnance créée.');
            return $this->redirectToRoute('medecin_ordonnances_index');
        }

        return $this->render('medecin/ordonnance/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ordonnances/{id}', name: 'medecin_ordonnances_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Ordonnance $ordonnance): Response
    {
        if ($ordonnance->getMedecin() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('medecin/ordonnance/show.html.twig', [
            'ordonnance' => $ordonnance,
        ]);
    }

    #[Route('/ordonnances/{id}/edit', name: 'medecin_ordonnances_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Ordonnance $ordonnance, Request $request, EntityManagerInterface $em, UserRepository $userRepo, MedicamentRepository $medicamentRepo): Response
    {
        if ($ordonnance->getMedecin() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(OrdonnanceType::class, $ordonnance, [
            'patients' => $userRepo->findPatients(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ne pas persister de ligne sans médicament valide (évite ForeignKeyConstraintViolationException)
            foreach ($ordonnance->getOrdonnanceMedicaments()->toArray() as $om) {
                $med = $om->getMedicament();
                if ($med === null || $med->getId() === null || $medicamentRepo->find($med->getId()) === null) {
                    $ordonnance->removeOrdonnanceMedicament($om);
                }
            }
            $em->flush();
            $this->addFlash('success', 'Ordonnance modifiée.');
            return $this->redirectToRoute('medecin_ordonnances_index');
        }

        return $this->render('medecin/ordonnance/edit.html.twig', [
            'form' => $form->createView(),
            'ordonnance' => $ordonnance,
        ]);
    }

    #[Route('/ordonnances/{id}/delete', name: 'medecin_ordonnances_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Ordonnance $ordonnance, Request $request, EntityManagerInterface $em): Response
    {
        if ($ordonnance->getMedecin() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_ordonnance_' . $ordonnance->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }
        $em->remove($ordonnance);
        $em->flush();
        $this->addFlash('success', 'Ordonnance supprimée.');
        return $this->redirectToRoute('medecin_ordonnances_index');
    }

    #[Route('/medicaments', name: 'medecin_medicaments_index', methods: ['GET'])]
    public function medicaments(MedicamentRepository $repo): Response
    {
        $medicaments = $repo->findAllOrderByNom();
        return $this->render('medecin/medicaments.html.twig', [
            'medicaments' => $medicaments,
        ]);
    }
}
