<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[] Returns an array of Task objects
     */
    public function findSortedByStartDate(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[] Returns an array of Task objects
     */
    public function findSortedByEndDate(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.endDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
