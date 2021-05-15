<?php

namespace App\Entity;

use App\Repository\LetterRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;

/**
 * @ORM\Entity(repositoryClass=LetterRepository::class)
 */
class Letter
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="letters")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $organization;

    /**
     * @ORM\ManyToOne(targetEntity="LetterStatus")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $barcodeNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fileName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $originalName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $size;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $created;

    /**
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;


    public function __toString()
    {
        return $this->getTitle().' '.$this->getOrganization()->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getStatus(): ?LetterStatus
    {
        return $this->status;
    }

    public function setStatus(?LetterStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getBarcodeNumber(): ?string
    {
        return $this->barcodeNumber;
    }

    public function setBarcodeNumber(?string $barcodeNumber): self
    {
        $this->barcodeNumber = $barcodeNumber;

        return $this;
    }


}
