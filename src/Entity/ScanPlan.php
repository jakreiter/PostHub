<?php

namespace App\Entity;

use App\Repository\ScanPlanRepository;
use Doctrine\ORM\Mapping as ORM;
use DH\Auditor\Provider\Doctrine\Auditing\Annotation as Audit;

/**
 * @ORM\Entity(repositoryClass=ScanPlanRepository::class)
 * @Audit\Auditable()
 * @Audit\Security(view={"ROLE_ADMIN"})
 */
class ScanPlan
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", nullable=false, nullable=false)
     */
    private $scan;

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

    public function getScan(): ?bool
    {
        return $this->scan;
    }

    public function setScan(bool $scan): self
    {
        $this->scan = $scan;

        return $this;
    }

}
