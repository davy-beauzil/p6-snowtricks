<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ImageRepository;
use App\Services\SecurityService;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: Types::STRING)]
    private string $id;

    #[ORM\Column(type: Types::TEXT)]
    private string $path;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(targetEntity: Trick::class, cascade: ['persist'])]
    private ?Trick $mainImageTrick = null;

    #[ORM\ManyToOne(targetEntity: Trick::class, cascade: ['persist'], inversedBy: 'images')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Trick $trick = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->id = SecurityService::generateRamdomId();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getMainImageTrick(): ?Trick
    {
        return $this->mainImageTrick;
    }

    public function setMainImageTrick(?Trick $trick): self
    {
        $this->mainImageTrick = $trick;
        return $this;
    }
}
