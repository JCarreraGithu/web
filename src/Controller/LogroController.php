<?php

namespace App\Controller;

use App\Entity\Logros;
use App\Form\LogroType;
use App\Repository\LogrosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/logros')]
class LogroController extends AbstractController
{
    #[Route('/', name: 'logros_lista')]
    public function index(LogrosRepository $repo): Response
    {
        $logros = $repo->findAll();

        return $this->render('logro/index.html.twig', [
            'logros' => $logros,
        ]);
    }

    #[Route('/nuevo', name: 'logro_nuevo')]
    public function nuevo(Request $request, EntityManagerInterface $em): Response
    {
        $logro = new Logros();
        $form = $this->createForm(LogroType::class, $logro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($logro);
            $em->flush();

            $this->addFlash('success', 'Logro registrado correctamente.');
            return $this->redirectToRoute('logros_lista');
        }

        return $this->render('logro/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
