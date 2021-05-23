<?php

namespace App\Entity;

use App\Repository\LetterStatusRepository;
use Doctrine\ORM\Mapping as ORM;
use DH\Auditor\Provider\Doctrine\Auditing\Annotation as Audit;

/**
 * @ORM\Entity(repositoryClass=LetterStatusRepository::class)
 * @Audit\Auditable()
 * @Audit\Security(view={"ROLE_ADMIN"})
 */
class LetterStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

}
