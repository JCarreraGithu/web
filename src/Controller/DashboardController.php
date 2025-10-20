<?php

namespace App\Controller;

use App\Entity\Empleados;
use App\Entity\Tiendas;
use App\Entity\Logros;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $totalEmpleados = $em->getRepository(Empleados::class)->count([]);
        $totalTiendas = $em->getRepository(Tiendas::class)->count([]);
        $totalLogrosPositivos = $em->getRepository(Logros::class)->count(['tipo' => 'positivo']);
        $totalLogrosNegativos = $em->getRepository(Logros::class)->count(['tipo' => 'negativo']);

        return $this->render('dashboard/index.html.twig', [
            'totalEmpleados' => $totalEmpleados,
            'totalTiendas' => $totalTiendas,
            'positivos' => $totalLogrosPositivos,
            'negativos' => $totalLogrosNegativos,
        ]);
    }
}
