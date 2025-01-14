<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProjectController extends AbstractController
{
    #[Route('/project/new', name: 'app_project_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project($this->getUser());

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/project/{id}', name: 'app_project_show')]
    public function show(Project $project): Response
    {
        $this->denyAccessUnlessGranted('access', $project);

        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/project/{id}/edit', name: 'app_project_edit')]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('access', $project);

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/project/{id}/delete', name: 'app_project_delete')]
    public function delete(Project $project, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('access', $project);

        $entityManager->remove($project);
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }
}