<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    #[Route('/task/new/{project}', name: 'app_task_new')]
    public function new(Request $request, Project $project, Task $parent = null, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('access', $project);

        $task = new Task();
        $task->setProject($project);
        $task->setParent($parent);

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('task/new.html.twig', [
            'form' => $form,
            'project' => $project,
            'parent' => $parent,
        ]);
    }

    #[Route('/task/{id}/edit', name: 'app_task_edit')]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('access', $task->getProject());

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_project_show', ['id' => $task->getProject()->getId()]);
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form,
            'task' => $task,
        ]);
    }

    #[Route('/task/{id}/toggle', name: 'app_task_toggle', methods: ['GET'])]
    public function toggleStatus(Task $task, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('access', $task->getProject());

        $task->setDone(!$task->isDone());

        if ($task->isDone() && !$task->getParent()) {
            foreach ($task->getSubTasks() as $subTask) {
                $subTask->setIsDone(true);
            }
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_project_show', ['id' => $task->getProject()->getId()]);
    }

    #[Route('/task/{id}/delete', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Task $task, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('access', $task->getProject());

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('app_project_show', ['id' => $task->getProject()->getId()]);
    }
}