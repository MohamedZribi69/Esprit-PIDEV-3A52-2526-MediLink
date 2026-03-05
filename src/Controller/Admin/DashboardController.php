<?php
// src/Controller/Admin/DashboardController.php
namespace App\Controller\Admin;

use App\Repository\MedicamentRepository;
use App\Repository\OrdonnanceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        MedicamentRepository $medicamentRepo,
        OrdonnanceRepository $ordonnanceRepo,
        UserRepository $userRepo
    ): Response
    {
        $stats = [
            'total_medicaments' => count($medicamentRepo->findAll()),
            'total_ordonnances' => count($ordonnanceRepo->findAll()),
            'total_users' => count($userRepo->findAll()),
            'ordonnances_recentes' => $ordonnanceRepo->findAllOrderByDate(),
        ];

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
        ]);
    }
}