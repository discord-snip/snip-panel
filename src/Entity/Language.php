<?php

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=LanguageRepository::class)
 */
class Language
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    private Uuid $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private string $name;

    public function __construct(string $name)
    {
        $this->id = Uuid::v4();
        $this->name = $name;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
