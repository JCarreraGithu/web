<?php

namespace App\Controller;

use App\Repository\EmpleadosRepository;
use App\Repository\LogrosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Logros;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReporteController extends AbstractController
{
    // =========================================================
    // DASHBOARD DE REPORTES
    // =========================================================
    #[Route('/reportes', name: 'reportes_dashboard')]
    public function index(EmpleadosRepository $empleadosRepo, LogrosRepository $logrosRepo): Response
    {
        $empleados = $empleadosRepo->findAll();

        $totalSalarios = 0;
        foreach ($empleados as $empleado) {
            $totalSalarios += (float) $empleado->getSalario();
        }

        // Notas:
        // - Si en BD hay "Positivo/Negativo" con mayúscula, puedes normalizar aquí.
        $positivos = count($logrosRepo->findBy(['tipo' => 'positivo']));
        $negativos = count($logrosRepo->findBy(['tipo' => 'negativo']));

        return $this->render('reporte/index.html.twig', [
            'empleados'      => $empleados,
            'totalSalarios'  => $totalSalarios,
            'positivos'      => $positivos,
            'negativos'      => $negativos,
        ]);
    }

    // =========================================================
    // REPORTE DE SALARIOS
    // =========================================================
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

        $agrupados = [];
        foreach ($empleados as $e) {
            $tienda = $e->getTienda()->getNombre();
            $agrupados[$tienda][] = $e;
        }
        ksort($agrupados);

        return $this->render('reporte/salarios.html.twig', [
            'agrupados' => $agrupados,
        ]);
    }

    #[Route('/reportes/salarios/pdf', name: 'reporte_salarios_pdf')]
    public function salariosPdf(EmpleadosRepository $empleadosRepo): Response
    {
        $empleados = $empleadosRepo->findAll();
        $agrupados = [];
        foreach ($empleados as $e) {
            $tienda = $e->getTienda()->getNombre();
            $agrupados[$tienda][] = $e;
        }
        ksort($agrupados);

        // Ojo: plantilla LIMPIA para PDF (sin extends)
        $html = $this->renderView('reporte/salarios_pdf.html.twig', [
            'agrupados' => $agrupados,
        ]);

        return $this->pdfResponse($html, 'reporte_salarios.pdf');
    }

    // =========================================================
    // REPORTE DE LOGROS
    // =========================================================
    #[Route('/reportes/logros', name: 'reportes_logros')]
    public function reportesLogros(EntityManagerInterface $em): Response
    {
        $logros = $em->getRepository(Logros::class)->findAll();

        $agrupados = [];
        foreach ($logros as $logro) {
            $emp = $logro->getEmpleado();
            $nombre = $emp ? ($emp->getNombre() . ' ' . $emp->getApellido()) : 'Sin empleado';
            $agrupados[$nombre][] = $logro;
        }
        ksort($agrupados);

        return $this->render('reporte/logros.html.twig', [
            'agrupados' => $agrupados,
        ]);
    }

    #[Route('/reportes/logros/pdf', name: 'reporte_logros_pdf')]
    public function logrosPdf(EntityManagerInterface $em): Response
    {
        $logros = $em->getRepository(Logros::class)->findAll();

        $agrupados = [];
        foreach ($logros as $logro) {
            $emp = $logro->getEmpleado();
            $nombre = $emp ? ($emp->getNombre() . ' ' . $emp->getApellido()) : 'Sin empleado';
            $agrupados[$nombre][] = $logro;
        }
        ksort($agrupados);

        // Plantilla PDF limpia
        $html = $this->renderView('reporte/logros_pdf.html.twig', [
            'agrupados' => $agrupados,
        ]);

        return $this->pdfResponse($html, 'reporte_logros.pdf');
    }

    // =========================================================
    // REPORTE DE LLAMADAS (NEGATIVOS)
    // =========================================================
    #[Route('/reportes/llamadas', name: 'reportes_llamadas')]
    public function reportesLlamadas(EntityManagerInterface $em): Response
    {
        // Si en BD hay ‘Negativo’ con mayúscula, normaliza:
        $logros = $em->getRepository(Logros::class)->findBy(['tipo' => 'negativo']);

        $agrupados = [];
        foreach ($logros as $logro) {
            $emp = $logro->getEmpleado();
            $nombre = $emp ? ($emp->getNombre() . ' ' . $emp->getApellido()) : 'Sin empleado';
            $agrupados[$nombre][] = $logro;
        }
        ksort($agrupados);

        return $this->render('reporte/llamadas.html.twig', [
            'agrupados' => $agrupados,
        ]);
    }

    #[Route('/reportes/llamadas/pdf', name: 'reporte_llamadas_pdf')]
    public function llamadasPdf(EntityManagerInterface $em): Response
    {
        $logros = $em->getRepository(Logros::class)->findBy(['tipo' => 'negativo']);

        $agrupados = [];
        foreach ($logros as $logro) {
            $emp = $logro->getEmpleado();
            $nombre = $emp ? ($emp->getNombre() . ' ' . $emp->getApellido()) : 'Sin empleado';
            $agrupados[$nombre][] = $logro;
        }
        ksort($agrupados);

        // Plantilla PDF limpia
        $html = $this->renderView('reporte/llamadas_pdf.html.twig', [
            'agrupados' => $agrupados,
        ]);

        return $this->pdfResponse($html, 'reporte_llamadas.pdf');
    }

    // =========================================================
    // Helper único para emitir PDF
    // =========================================================
    private function pdfResponse(string $html, string $filename): Response
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans'); // soporta tildes/ñ

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Mostrar en navegador (no forzar descarga)
        $dompdf->stream($filename, ['Attachment' => false]);

        return new Response('', 200, ['Content-Type' => 'application/pdf']);
    }
}
