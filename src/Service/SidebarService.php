<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class SidebarService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function getSidebarData(User $user): array
    {
        $tasks = $user->getTasks();
        $projects = $user->getProjects();

        return [
            'projects' => $this->getProjectsData($projects),
            'tasks' => $this->getTasksData($tasks),
            'upcoming' => $this->getUpcomingData($tasks),
        ];
    }

    /**
     * @param Collection<Project> $projects
     * @return Project[]
     */
    private function getProjectsData(Collection $projects): array
    {
        return array_map(function(Project $project) {
            return [
                'id' => $project->getId(),
                'image_path' => 'images/project.svg',
                'alt' => 'Project',
                'name' => $project->getName(),
                'count' => $project->getTasks()->count(),
                'link' => $this->urlGenerator->generate('app_project_show', ['id' => $project->getId()]),
            ];
        }, $projects->toArray());
    }

    /**
     * @param Collection<Task> $tasks
     * @return Task[]
     */
    private function getTasksData(Collection $tasks): array
    {
        $data = [
            [
                'image_path' => 'images/clock.svg',
                'alt' => 'Open Tasks',
                'name' => 'Open Tasks',
                'count' => $tasks->filter(fn($task) => !$task->isDone())->count(),
                'link' => $this->urlGenerator->generate('app_task_open'),
            ],
            [
                'image_path' => 'images/check.svg',
                'alt' => 'Done Tasks',
                'name' => 'Done Tasks',
                'count' => $tasks->filter(fn($task) => $task->isDone())->count(),
                'link' => $this->urlGenerator->generate('app_task_done'),
            ],
            [
                'image_path' => 'images/list-check.svg',
                'alt' => 'All Tasks',
                'name' => 'All Tasks',
                'count' => $tasks->count(),
                'link' => $this->urlGenerator->generate('app_task_all'),
            ],
        ];

        return $data;
    }

    /**
     * @param Collection<Task> $tasks
     * @return Task[]
     */
    private function getUpcomingData(Collection $tasks): array
    {
        $today = (new \DateTime())->setTime(0, 0)->getTimestamp();
        $tomorrow = (new \DateTime())->modify('+1 day')->setTime(0, 0)->getTimestamp();
        $endOfWeek = (new \DateTime())->modify('next sunday')->setTime(0, 0)->getTimestamp();
        $endOfNextWeek = (new \DateTime())->modify('next sunday')->modify('+1 week')->setTime(0, 0)->getTimestamp();

        $data = [
            [
                'image_path' => 'images/calendar-check.svg',
                'alt' => 'Today',
                'name' => 'Today',
                'count' => $tasks->filter(fn($task) => $task->getNormalizedStartDayTimestamp() === $today)->count(),
                'link' => $this->urlGenerator->generate('app_task_today'),
            ],
            [
                'image_path' => 'images/calendar-check.svg',
                'alt' => 'Tomorrow',
                'name' => 'Tomorrow',
                'count' => $tasks->filter(fn($task) => $task->getNormalizedStartDayTimestamp() === $tomorrow)->count(),
                'link' => $this->urlGenerator->generate('app_task_tomorrow'),
            ],
            [
                'image_path' => 'images/calendar-check.svg',
                'alt' => 'This Week',
                'name' => 'This Week',
                'count' => $tasks->filter(fn($task) => $task->getNormalizedStartDayTimestamp() >= $today && $task->getNormalizedStartDayTimestamp() <= $endOfWeek)->count(),
                'link' => $this->urlGenerator->generate('app_task_this_week'),
            ],
            [
                'image_path' => 'images/calendar-check.svg',
                'alt' => 'Next Week',
                'name' => 'Next Week',
                'count' => $tasks->filter(fn($task) => $task->getNormalizedStartDayTimestamp() >= $endOfWeek && $task->getNormalizedStartDayTimestamp() <= $endOfNextWeek)->count(),
                'link' => $this->urlGenerator->generate('app_task_next_week'),
            ]
        ];

        $today = (new \DateTime())->setTime(0, 0)->getTimestamp();
        $overdue = $tasks->filter(fn($task) => $task->getEndDateTimestamp() < $today && !$task->isDone())->count();
        if ($overdue > 0) {
            array_unshift($data, [
                'image_path' => 'images/triangle-exclamation.svg',
                'alt' => 'Overdue',
                'name' => 'Overdue',
                'count' => $overdue,
                'link' => $this->urlGenerator->generate('app_task_overdue'),
            ]);
        }

        return $data;
    }
}