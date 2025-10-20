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

        // Formulario incluido dentro del modal
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
            $fotoFile = $form->get('fotografia')->getData();

            if ($fotoFile) {
                $originalFilename = pathinfo($fotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$fotoFile->guessExtension();

                try {
                    $fotoFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen.');
                    return $this->redirectToRoute('empleados_lista');
                }

                $empleado->setFotografia($newFilename);
            }

            $empleado->setCreatedAt(new \DateTimeImmutable());
            $em->persist($empleado);
            $em->flush();

            $this->addFlash('success', 'Empleado registrado correctamente.');
        } else {
            $this->addFlash('error', 'Revisa los campos del formulario.');
        }

        return $this->redirectToRoute('empleados_lista');
    }

    #[Route('/empleado/{id}', name: 'empleado_ver', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function ver(Empleados $empleado): Response
    {
        return $this->render('empleado/ver.html.twig', [
            'e' => $empleado,
        ]);
    }

    #[Route('/empleado/{id}/editar', name: 'empleado_editar', requirements: ['id' => '\d+'])]
    public function editar(Empleados $empleado, Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EmpleadoType::class, $empleado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fotoFile = $form->get('fotografia')->getData();

            if ($fotoFile) {
                $originalFilename = pathinfo($fotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$fotoFile->guessExtension();

                try {
                    $fotoFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen.');
                }

                $empleado->setFotografia($newFilename);
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
