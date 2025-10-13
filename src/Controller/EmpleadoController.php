<?php

namespace App\Controller;

use App\Entity\Empleados;
use App\Form\EmpleadoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmpleadoController extends AbstractController
{
    #[Route('/empleado/nuevo', name: 'empleado_nuevo')]
    public function nuevo(Request $request, EntityManagerInterface $em): Response
    {
        $empleado = new Empleados();
        $form = $this->createForm(EmpleadoType::class, $empleado);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $foto = $form->get('fotografia')->getData();
            if ($foto) {
                $nombreArchivo = uniqid() . '.' . $foto->guessExtension();
                $foto->move($this->getParameter('uploads_dir'), $nombreArchivo);
                $empleado->setFotografia($nombreArchivo);
            }

            $empleado->setCreatedAt(new \DateTimeImmutable());

            $em->persist($empleado);
            $em->flush();

            $this->addFlash('success', 'Empleado registrado correctamente.');

            return $this->redirectToRoute('empleado_nuevo');
        }

        return $this->render('empleado/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/empleado/lista', name: 'empleado_lista')]
    public function lista(EntityManagerInterface $em): Response
    {
        $empleados = $em->getRepository(Empleados::class)->findAll();

        return $this->render('empleado/lista.html.twig', [
            'empleados' => $empleados,
        ]);
    }
}
