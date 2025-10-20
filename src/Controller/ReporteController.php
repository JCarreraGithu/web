<?php

namespace App\Controller;

use App\Repository\EmpleadosRepository;
use App\Repository\LogrosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Logros;
use Doctrine\ORM\EntityManagerInterface;


class ReporteController extends AbstractController
{
    #[Route('/reportes', name: 'reportes_dashboard')]
    public function index(EmpleadosRepository $empleadosRepo, LogrosRepository $logrosRepo): Response
    {
        // 1️⃣ Lista general de empleados
        $empleados = $empleadosRepo->findAll();

        // 2️⃣ Total de salarios
        $totalSalarios = 0;
        foreach ($empleados as $empleado) {
            $totalSalarios += $empleado->getSalario();
        }

        // 3️⃣ Logros agrupados
        $positivos = count($logrosRepo->findBy(['tipo' => 'positivo']));
        $negativos = count($logrosRepo->findBy(['tipo' => 'negativo']));

        return $this->render('reporte/index.html.twig', [
            'empleados' => $empleados,
            'totalSalarios' => $totalSalarios,
            'positivos' => $positivos,
            'negativos' => $negativos,
        ]);
    }

    #[Route('/reportes/salarios', name: 'reportes_salarios')]
    public function salariosPorTienda(EmpleadosRepository $empleadosRepo): Response
{
    $empleados = $empleadosRepo->createQueryBuilder('e')
        ->join('e.tienda', 't')
        ->addSelect('t')
        ->orderBy('t.nombre', 'ASC')
        ->addOrderBy('e.salario', 'DESC')
        ->getQuery()
        ->getResult();

    // Agrupamos empleados por tienda
    $agrupados = [];
    foreach ($empleados as $e) {
        $tienda = $e->getTienda()->getNombre();
        $agrupados[$tienda][] = $e;
    }

    return $this->render('reporte/salarios.html.twig', [
        'agrupados' => $agrupados,
    ]);
}

#[Route('/reportes/logros', name: 'reportes_logros')]
public function reportesLogros(EntityManagerInterface $em): Response
{
    $logros = $em->getRepository(Logros::class)->findAll();

    // Agrupar logros por empleado
    $agrupados = [];
    foreach ($logros as $logro) {
        $empleado = $logro->getEmpleado()->getNombre() . ' ' . $logro->getEmpleado()->getApellido();
        $agrupados[$empleado][] = $logro;
    }

    return $this->render('reporte/logros.html.twig', [
        'agrupados' => $agrupados,
    ]);
}

#[Route('/reportes/llamadas', name: 'reportes_llamadas')]
public function reportesLlamadas(EntityManagerInterface $em): Response
{
    $logros = $em->getRepository(Logros::class)->findBy(['tipo' => 'negativo']);

    // Agrupar por empleado
    $agrupados = [];
    foreach ($logros as $logro) {
        $empleado = $logro->getEmpleado()->getNombre() . ' ' . $logro->getEmpleado()->getApellido();
        $agrupados[$empleado][] = $logro;
    }

    return $this->render('reporte/llamadas.html.twig', [
        'agrupados' => $agrupados,
    ]);
}



}
