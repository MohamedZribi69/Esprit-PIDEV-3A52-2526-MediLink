<?php

namespace App\Controller\Patient;

use App\Entity\Disponibilite;
use App\Entity\RendezVous;
use App\Repository\DisponibiliteRepository;
use App\Repository\RendezVousRepository;
use App\Service\RendezVousService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/patient')]
#[IsGranted('ROLE_USER')]
final class PatientRendezVousController extends AbstractController
{
    #[Route('/rendez-vous', name: 'patient_rendezvous_index')]
    public function index(Request $request, DisponibiliteRepository $dispoRepo, RendezVousRepository $rvRepo, PaginatorInterface $paginator): Response
    {
        $patient = $this->getUser();
        $disponibilites = $dispoRepo->findLibresAvenirAvecMedecin();

        $qbRdv = $rvRepo->getFindByPatientQueryBuilder($patient);
        $paginationRdv = $paginator->paginate($qbRdv, $request->query->getInt('page', 1), 10);

        $statutsLabels = array_flip(RendezVous::getStatuts());

        return $this->render('patient/rendezvous_index.html.twig', [
            'disponibilites' => $disponibilites,
            'paginationRdv' => $paginationRdv,
            'statutsLabels' => $statutsLabels,
        ]);
    }

    #[Route('/rendez-vous/reserver/{id}', name: 'patient_rendezvous_reserver', methods: ['POST'])]
    public function reserver(Disponibilite $disponibilite, Request $request, RendezVousService $rdvService): Response
    {
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('reserver_' . $disponibilite->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }

        if ($disponibilite->getMedecin() === null) {
            $this->addFlash('error', 'Disponibilité invalide.');
            return $this->redirectToRoute('patient_rendezvous_index');
        }

        try {
            $rdvService->creerRendezVous($disponibilite, $this->getUser());
            $this->addFlash('success', 'Rendez-vous réservé avec succès !');
        } catch (\DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('patient_rendezvous_index');
    }
}
