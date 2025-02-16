<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[
    Entity,
    Table(name: 'images'),
    Index(name: 'idx_hash', columns: ['hash']),
    Index(name: 'idx_active', columns: ['is_active']),
    HasLifecycleCallbacks
]
class Image
{
    #[Id, Column(type: 'integer'), GeneratedValue]
    public int $id;

    #[Column]
    public string $hash;

    #[Column]
    public string $extension;

    #[Column]
    public int $size;

    #[Column(type: Types::SMALLINT, nullable: true)]
    public ?int $minutes = null;

    #[Column(type: Types::TEXT, nullable: true)]
    public ?string $password = null;

    #[Column(type: Types::TEXT, nullable: true)]
    public ?string $message = null;

    #[Column(type: Types::TEXT, length: 42)]
    public string $ip;

    #[Column]
    public bool $isActive = true;

    #[Column]
    public bool $isTimeout = false;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $updatedAt = null;
    
    public ?UploadedFile $image = null;

    #[PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }

    #[PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
