<?php

namespace App\Controller;

use App\Repository\EmpleadosRepository;
use App\Repository\LogrosRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Logros;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
            $totalSalarios += $empleado->getSalario();
        }

        $positivos = count($logrosRepo->findBy(['tipo' => 'positivo']));
        $negativos = count($logrosRepo->findBy(['tipo' => 'negativo']));

        return $this->render('reporte/index.html.twig', [
            'empleados' => $empleados,
            'totalSalarios' => $totalSalarios,
            'positivos' => $positivos,
            'negativos' => $negativos,
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

        return $this->render('reporte/salarios.html.twig', [
            'agrupados' => $agrupados,
        ]);
    }

    #[Route('/reportes/salarios/xlsx', name: 'reporte_salarios_xlsx')]
    public function salariosXlsx(EntityManagerInterface $em): Response
    {
        $empleados = $em->getRepository(\App\Entity\Empleados::class)->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Salarios por Tienda');
        $sheet->setCellValue('A1', 'Tienda');
        $sheet->setCellValue('B1', 'Empleado');
        $sheet->setCellValue('C1', 'Puesto');
        $sheet->setCellValue('D1', 'Salario');

        $row = 2;
        foreach ($empleados as $e) {
            $sheet->setCellValue("A$row", $e->getTienda()->getNombre());
            $sheet->setCellValue("B$row", $e->getNombre() . ' ' . $e->getApellido());
            $sheet->setCellValue("C$row", $e->getPuesto()->getNombre());
            $sheet->setCellValue("D$row", $e->getSalario());
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'salarios_') . '.xlsx';
        $writer->save($tempFile);

        return $this->file($tempFile, 'reporte_salarios.xlsx', Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
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

        $html = $this->renderView('reporte/salarios.html.twig', [
            'agrupados' => $agrupados,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reporte_salarios.pdf"'
        ]);
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
            $empleado = $logro->getEmpleado()->getNombre() . ' ' . $logro->getEmpleado()->getApellido();
            $agrupados[$empleado][] = $logro;
        }

        return $this->render('reporte/logros.html.twig', [
            'agrupados' => $agrupados,
        ]);
    }

    #[Route('/reportes/logros/xlsx', name: 'reporte_logros_xlsx')]
    public function logrosXlsx(EntityManagerInterface $em): Response
    {
        $logros = $em->getRepository(Logros::class)->findAll();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Logros');
        $sheet->setCellValue('A1', 'Empleado');
        $sheet->setCellValue('B1', 'Descripción');
        $sheet->setCellValue('C1', 'Tipo');
        $sheet->setCellValue('D1', 'Fecha');

        $row = 2;
        foreach ($logros as $l) {
            $sheet->setCellValue("A$row", $l->getEmpleado()->getNombre() . ' ' . $l->getEmpleado()->getApellido());
            $sheet->setCellValue("B$row", $l->getDescription());
            $sheet->setCellValue("C$row", ucfirst($l->getTipo()));
            $sheet->setCellValue("D$row", $l->getFechaOcurrencia()->format('d/m/Y'));
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'logros_') . '.xlsx';
        $writer->save($tempFile);

        return $this->file($tempFile, 'reporte_logros.xlsx', Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }

    #[Route('/reportes/logros/pdf', name: 'reporte_logros_pdf')]
    public function logrosPdf(EntityManagerInterface $em): Response
    {
        $logros = $em->getRepository(Logros::class)->findAll();
        $agrupados = [];

        foreach ($logros as $logro) {
            $empleado = $logro->getEmpleado()->getNombre() . ' ' . $logro->getEmpleado()->getApellido();
            $agrupados[$empleado][] = $logro;
        }

        $html = $this->renderView('reporte/logros.html.twig', [
            'agrupados' => $agrupados,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reporte_logros.pdf"'
        ]);
    }

    // =========================================================
    // REPORTE DE LLAMADAS
    // =========================================================
    #[Route('/reportes/llamadas', name: 'reportes_llamadas')]
    public function reportesLlamadas(EntityManagerInterface $em): Response
    {
        $logros = $em->getRepository(Logros::class)->findBy(['tipo' => 'negativo']);
        $agrupados = [];

        foreach ($logros as $logro) {
            $empleado = $logro->getEmpleado()->getNombre() . ' ' . $logro->getEmpleado()->getApellido();
            $agrupados[$empleado][] = $logro;
        }

        return $this->render('reporte/llamadas.html.twig', [
            'agrupados' => $agrupados,
        ]);
    }

    #[Route('/reportes/llamadas/xlsx', name: 'reporte_llamadas_xlsx')]
    public function llamadasXlsx(EntityManagerInterface $em): Response
    {
        $logros = $em->getRepository(Logros::class)->findBy(['tipo' => 'negativo']);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Llamadas de Atención');
        $sheet->setCellValue('A1', 'Empleado');
        $sheet->setCellValue('B1', 'Descripción');
        $sheet->setCellValue('C1', 'Fecha');

        $row = 2;
        foreach ($logros as $l) {
            $sheet->setCellValue("A$row", $l->getEmpleado()->getNombre() . ' ' . $l->getEmpleado()->getApellido());
            $sheet->setCellValue("B$row", $l->getDescription());
            $sheet->setCellValue("C$row", $l->getFechaOcurrencia()->format('d/m/Y'));
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'llamadas_') . '.xlsx';
        $writer->save($tempFile);

        return $this->file($tempFile, 'reporte_llamadas.xlsx', Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }

    #[Route('/reportes/llamadas/pdf', name: 'reporte_llamadas_pdf')]
    public function llamadasPdf(EntityManagerInterface $em): Response
    {
        $logros = $em->getRepository(Logros::class)->findBy(['tipo' => 'negativo']);
        $agrupados = [];

        foreach ($logros as $logro) {
            $empleado = $logro->getEmpleado()->getNombre() . ' ' . $logro->getEmpleado()->getApellido();
            $agrupados[$empleado][] = $logro;
        }

        $html = $this->renderView('reporte/llamadas.html.twig', [
            'agrupados' => $agrupados,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reporte_llamadas.pdf"'
        ]);
    }
}
