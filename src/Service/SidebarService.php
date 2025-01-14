<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class SidebarService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function getSidebarData(User $user): array
    {
        return [
            'projects' => $this->getProjectsData($user),
            'todos' => $this->getTasksData()
        ];
    }

    private function getProjectsData(User $user): array
    {
        return array_map(function($project) {
            return [
                'id' => $project->getId(),
                'image_path' => 'images/project.svg',
                'alt' => 'Project',
                'name' => $project->getName(),
                'count' => 0,
                'link' => $this->urlGenerator->generate('app_project_show', ['id' => $project->getId()]),
            ];
        }, $user->getProjects()->toArray());
    }

    private function getTasksData(): array
    {
        return [
            [
                'image_path' => 'images/check.svg',
                'alt' => 'All Tasks',
                'name' => 'All Tasks',
                'count' => 0,
                'link' => '#',
            ]
        ];
    }
}