<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Todo;
use App\Form\TodoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TodoController extends AbstractController
{
    #[Route('/todo/new/{project}', name: 'app_todo_new')]
    public function new(Request $request, Project $project, Todo $parent = null, EntityManagerInterface $entityManager): Response
    {
        $todo = new Todo();
        $todo->setProject($project);
        $todo->setParent($parent);

        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($todo);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('todo/new.html.twig', [
            'form' => $form,
            'project' => $project,
            'parent' => $parent,
        ]);
    }

    #[Route('/todo/{id}/edit', name: 'app_todo_edit')]
    public function edit(Request $request, Todo $todo, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $todo->getProject());

        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_project_show', ['id' => $todo->getProject()->getId()]);
        }

        return $this->render('todo/edit.html.twig', [
            'form' => $form,
            'todo' => $todo,
        ]);
    }

    #[Route('/todo/{id}/toggle', name: 'app_todo_toggle', methods: ['GET'])]
    public function toggleStatus(Todo $todo, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $todo->getProject());

        $todo->setDone(!$todo->isDone());

        if ($todo->isDone() && !$todo->getParent()) {
            foreach ($todo->getSubTodos() as $subTodo) {
                $subTodo->setIsDone(true);
            }
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_project_show', ['id' => $todo->getProject()->getId()]);
    }

    #[Route('/todo/{id}/delete', name: 'app_todo_delete', methods: ['POST'])]
    public function delete(Request $request, Todo $todo, EntityManagerInterface $entityManager): Response
    {
        //if ($this->isCsrfTokenValid('delete_todo_' . $todo->getId(), $request->request->get('_token'))) {
        $entityManager->remove($todo);
        $entityManager->flush();
        //}

        return $this->redirectToRoute('app_project_show', ['id' => $todo->getProject()->getId()]);
    }
}