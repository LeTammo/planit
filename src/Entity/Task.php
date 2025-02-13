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

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(nullable: false)]
    private ?bool $isDone = false;

    #[ORM\Column(nullable: false)]
    private ?bool $hasDate = true;

    #[ORM\Column(nullable: false)]
    private ?bool $hasRange = false;

    #[ORM\Column(nullable: false)]
    private ?bool $hasTime = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subTasks')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', orphanRemoval: true)]
    private Collection $subTasks;

    public function __construct()
    {
        $this->endDate = (new \DateTime('tomorrow'))->setTime(10, 0);
        $this->startDate = (new \DateTime('today'))->setTime(9, 0);
        $this->subTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function hasDate(): bool
    {
        return $this->hasDate;
    }

    public function setHasDate(bool $hasDate): static
    {
        $this->hasDate = $hasDate;
        return $this;
    }

    public function hasRange(): bool
    {
        return $this->hasRange;
    }

    public function setHasRange(bool $hasRange): static
    {
        $this->hasRange = $hasRange;
        return $this;
    }

    public function hasTime(): bool
    {
        return $this->hasTime;
    }

    public function setHasTime(bool $hasTime): static
    {
        $this->hasTime = $hasTime;
        return $this;
    }

    public function getStartDate(): \DateTimeInterface
    {
        if ($this->subTasks->isEmpty()) {
            return $this->startDate;
        }

        $earliestStartDate = null;
        foreach ($this->subTasks as $subTask) {
            if ($subTask->getStartDate() < $earliestStartDate || $earliestStartDate === null) {
                $earliestStartDate = $subTask->getStartDate();
            }
        }

        return $earliestStartDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getStartDateTimestamp(): int
    {
        return $this->getStartDate()->getTimestamp();
    }

    public function getStartDay(): string
    {
        return $this->getStartDate()->format('Y-m-d');
    }

    public function getStartTime(): string
    {
        return $this->getStartDate()->format('H:i');
    }

    public function getNormalizedStartDayTimestamp()
    {
        return $this->getStartDate()->setTime(0, 0)->getTimestamp();
    }

    public function getEndDate(): \DateTimeInterface
    {
        if ($this->subTasks->isEmpty()) {
            return $this->endDate;
        }

        $latestEndDate = null;
        foreach ($this->subTasks as $subTask) {
            if ($subTask->getEndDate() > $latestEndDate || $latestEndDate === null) {
                $latestEndDate = $subTask->getEndDate();
            }
        }

        return $latestEndDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getEndDateTimestamp(): int
    {
        return $this->getEndDate()->getTimestamp();
    }

    public function getEndDay(): string
    {
        return $this->getEndDate()->format('Y-m-d');
    }

    public function getEndTime(): string
    {
        return $this->getEndDate()->format('H:i');
    }

    public function getNormalizedEndDayTimestamp()
    {
        return $this->getEndDate()->setTime(0, 0)->getTimestamp();
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
        $criteria = Criteria::create()->orderBy(['startDate' => Order::Ascending]);

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
