<?php

namespace App\Entity;

use App\Repository\TodoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TodoRepository::class)]
class Todo
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

    #[ORM\ManyToOne(inversedBy: 'todos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subTodos')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', orphanRemoval: true)]
    private Collection $subTodos;

    public function __construct()
    {
        $this->subTodos = new ArrayCollection();
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
        return $this->dueDate;
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
        return $this->isDone;
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

    public function getSubTodos(): Collection
    {
        return $this->subTodos;
    }

    public function addSubTodo(Todo $subTodo): static
    {
        if (!$this->subTodos->contains($subTodo)) {
            $this->subTodos->add($subTodo);
            $subTodo->setParent($this);
        }

        return $this;
    }

    public function removeSubTodo(Todo $subTodo): static
    {
        if ($this->subTodos->removeElement($subTodo)) {
            if ($subTodo->getParent() === $this) {
                $subTodo->setParent(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
