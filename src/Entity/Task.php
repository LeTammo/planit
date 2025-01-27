<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isDone = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subTasks')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', orphanRemoval: true)]
    private Collection $subTasks;

    public function __construct()
    {
        $this->subTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        if ($this->subTasks->isEmpty()) {
            return $this->dueDate;
        }

        $latestDueDate = null;
        foreach ($this->subTasks as $subTask) {
            if (!$subTask->isDone() && ($subTask->getDueDate() < $latestDueDate || $latestDueDate === null)) {
                $latestDueDate = $subTask->getDueDate();
            }
        }

        return $latestDueDate;
    }

    public function getDueDateTimestamp(): int
    {
        return $this->getDueDate()->getTimestamp();
    }

    public function getDueDay(): string
    {
        return $this->getDueDate()->format('Y-m-d');
    }

    public function getDueDayTimestamp(): int
    {
        return (clone $this->getDueDate())->setTime(0, 0)->getTimestamp();
    }

    public function getDueTime(): string
    {
        return $this->getDueDate()->format('H:i');
    }

    public function setDueDate(?\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function isDone(): ?bool
    {
        if ($this->subTasks->isEmpty()) {
            return $this->isDone;
        }

        foreach ($this->subTasks as $subTask) {
            if (!$subTask->isDone()) {
                return false;
            }
        }

        return true;
    }

    public function setDone(bool $isDone): static
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getSubTasks(): Collection
    {
        $criteria = Criteria::create()->orderBy(['dueDate' => Order::Ascending]);

        return $this->subTasks->matching($criteria);
    }

    public function addSubTask(Task $subTask): static
    {
        if (!$this->subTasks->contains($subTask)) {
            $this->subTasks->add($subTask);
            $subTask->setParent($this);
        }

        return $this;
    }

    public function removeSubTask(Task $subTask): static
    {
        if ($this->subTasks->removeElement($subTask)) {
            if ($subTask->getParent() === $this) {
                $subTask->setParent(null);
            }
        }

        return $this;
    }

    public function getDoneSubTasks(): Collection
    {
        return $this->subTasks->filter(fn (Task $task) => $task->isDone());
    }

    public function getUndoneSubTasks(): Collection
    {
        return $this->subTasks->filter(fn (Task $task) => !$task->isDone());
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
