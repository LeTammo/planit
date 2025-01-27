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
    #[Route('/task/new/{project}/{parent?}', name: 'app_task_new')]
    public function new(Request $request, Project $project, ?Task $parent = null, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('access', $project);

        $task = new Task();
        $task->setProject($project);
        $task->setParent($parent);

        $time = new \DateTime();
        $time->modify('+1 day');
        $time->setTime($time->format('H'), 0);
        $task->setDueDate($time);

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
            'task' => $task,
        ]);
    }

    #[Route('/task/{id}/edit', name: 'app_task_edit')]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('access', $task->getProject());

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();
            return $this->redirectToRoute('app_project_show', ['id' => $task->getProject()->getId()]);
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form,
            'task' => $task,
            'initialTime' => $task->getDueDate()->format('H:i'),
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

    #[Route('/task/all', name: 'app_task_all')]
    public function all(): Response
    {
        $tasks = $this->getUser()->getTasks();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'listTitle' => 'All Tasks',
        ]);
    }

    #[Route('/task/open', name: 'app_task_open')]
    public function open(): Response
    {
        $tasks = $this->getUser()->getTasks();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks->filter(fn(Task $task) => !$task->isDone()),
            'listTitle' => 'Open Tasks',
        ]);
    }

    #[Route('/task/done', name: 'app_task_done')]
    public function done(): Response
    {
        $tasks = $this->getUser()->getTasks();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks->filter(fn(Task $task) => $task->isDone()),
            'listTitle' => 'Done Tasks',
        ]);
    }

    #[Route('/task/overdue', name: 'app_task_overdue')]
    public function overdue(): Response
    {
        $tasks = $this->getUser()->getTasks();
        $today = (new \DateTime())->setTime(0, 0)->getTimestamp();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks->filter(fn(Task $task) => $task->getDueDate() && $task->getDueDayTimestamp() < $today && !$task->isDone()),
            'listTitle' => 'Overdue',
        ]);
    }

    #[Route('/task/today', name: 'app_task_today')]
    public function today(): Response
    {
        $tasks = $this->getUser()->getTasks();
        $today = (new \DateTime())->setTime(0, 0)->getTimestamp();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks->filter(fn(Task $task) => $task->getDueDate() && $task->getDueDayTimestamp() == $today),
            'listTitle' => 'Today',
        ]);
    }

    #[Route('/task/tomorrow', name: 'app_task_tomorrow')]
    public function tomorrow(): Response
    {
        $tasks = $this->getUser()->getTasks();
        $tomorrow = (new \DateTime())->setTime(0, 0)->modify('+1 day')->getTimestamp();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks->filter(fn(Task $task) => $task->getDueDate() && $task->getDueDayTimestamp() == $tomorrow),
            'listTitle' => 'Tomorrow',
        ]);
    }

    #[Route('/task/this-week', name: 'app_task_this_week')]
    public function thisWeek(): Response
    {
        $tasks = $this->getUser()->getTasks();
        $today = (new \DateTime())->setTime(0, 0)->getTimestamp();
        $endOfWeek = (new \DateTime())->modify('next sunday')->setTime(0, 0)->getTimestamp();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks->filter(fn(Task $task) => $task->getDueDate() && $task->getDueDayTimestamp() >= $today && $task->getDueDayTimestamp() <= $endOfWeek),
            'listTitle' => 'This Week',
        ]);
    }

    #[Route('/task/next-week', name: 'app_task_next_week')]
    public function nextWeek(): Response
    {
        $tasks = $this->getUser()->getTasks();
        $endOfWeek = (new \DateTime())->modify('next sunday')->setTime(0, 0)->getTimestamp();
        $endOfNextWeek = (new \DateTime())->modify('next sunday')->modify('+1 week')->setTime(0, 0)->getTimestamp();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks->filter(fn(Task $task) => $task->getDueDate() && $task->getDueDayTimestamp() >= $endOfWeek && $task->getDueDayTimestamp() <= $endOfNextWeek),
            'listTitle' => 'Next Week',
        ]);
    }
}