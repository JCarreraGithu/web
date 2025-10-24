<?php

namespace App\Controller;

use App\Entity\Empleados;
use App\Form\EmpleadoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class EmpleadoController extends AbstractController
{
    #[Route('/empleados', name: 'empleados_lista', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $empleados = $em->getRepository(Empleados::class)->findAll();

        $form = $this->createForm(EmpleadoType::class, new Empleados(), [
            'action' => $this->generateUrl('empleado_nuevo'),
            'method' => 'POST',
        ]);

        return $this->render('empleado/lista.html.twig', [
            'empleados' => $empleados,
            'form'      => $form->createView(),
        ]);
    }

    #[Route('/empleado/nuevo', name: 'empleado_nuevo', methods: ['POST'])]
    public function nuevo(Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        $empleado = new Empleados();
        $form = $this->createForm(EmpleadoType::class, $empleado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $uploadsDir = $this->getParameter('uploads_directory');

            // Asegurar carpeta y probar escritura con un archivo temporal
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0777, true);
            }
            $test = $uploadsDir . DIRECTORY_SEPARATOR . '.perm_test';
            if (@file_put_contents($test, 'ok') === false) {
                $this->addFlash('error', 'Error al subir la imagen: La carpeta de uploads no es escribible: ' . $uploadsDir);
                return $this->redirectToRoute('empleados_lista');
            }
            @unlink($test);

            // Subida de imagen (opcional)
            $fotoFile = $form->get('fotografia')->getData();
            if ($fotoFile) {
                $originalFilename = pathinfo($fotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = (string) $slugger->slug($originalFilename);
                $extension = $fotoFile->guessExtension() ?: 'bin';
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $fotoFile->move($uploadsDir, $newFilename);
                    $empleado->setFotografia($newFilename);
                } catch (\Throwable $e) {
                    $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                    return $this->redirectToRoute('empleados_lista');
                }
            }

            $empleado->setCreatedAt(new \DateTimeImmutable());
            $em->persist($empleado);
            $em->flush();

            $this->addFlash('success', 'Empleado registrado correctamente.');
            return $this->redirectToRoute('empleados_lista');
        }

        $this->addFlash('error', 'Revisa los campos del formulario.');
        return $this->redirectToRoute('empleados_lista');
    }

    #[Route('/empleado/{id}', name: 'empleado_ver', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function ver(Empleados $empleado): Response
    {
        return $this->render('empleado/_detalle.html.twig', [
            'e' => $empleado,
        ]);
    }

    #[Route('/empleado/{id}/editar', name: 'empleado_editar', requirements: ['id' => '\d+'])]
    public function editar(Empleados $empleado, Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EmpleadoType::class, $empleado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $uploadsDir = $this->getParameter('uploads_directory');

            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0777, true);
            }
            $test = $uploadsDir . DIRECTORY_SEPARATOR . '.perm_test';
            if (@file_put_contents($test, 'ok') === false) {
                $this->addFlash('error', 'Error al subir la imagen: La carpeta de uploads no es escribible: ' . $uploadsDir);
                return $this->redirectToRoute('empleados_lista');
            }
            @unlink($test);

            $fotoFile = $form->get('fotografia')->getData();
            if ($fotoFile) {
                $originalFilename = pathinfo($fotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = (string) $slugger->slug($originalFilename);
                $extension = $fotoFile->guessExtension() ?: 'bin';
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                try {
                    $fotoFile->move($uploadsDir, $newFilename);
                    $empleado->setFotografia($newFilename);
                } catch (\Throwable $e) {
                    $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                    return $this->redirectToRoute('empleados_lista');
                }
            }

            $em->flush();
            $this->addFlash('success', 'Empleado actualizado correctamente.');
            return $this->redirectToRoute('empleados_lista');
        }

        return $this->render('empleado/editar.html.twig', [
            'form' => $form->createView(),
            'e'    => $empleado,
        ]);
    }

    #[Route('/empleado/{id}/eliminar', name: 'empleado_eliminar', requirements: ['id' => '\d+'])]
    public function eliminar(Empleados $empleado, EntityManagerInterface $em): Response
    {
        $em->remove($empleado);
        $em->flush();

        $this->addFlash('success', 'Empleado eliminado correctamente.');
        return $this->redirectToRoute('empleados_lista');
    }
}
