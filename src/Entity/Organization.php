<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DH\Auditor\Provider\Doctrine\Auditing\Annotation as Audit;


/**
 * @ORM\Entity(repositoryClass=OrganizationRepository::class)
 * @ORM\Table(indexes={
 *      @ORM\Index(name="created_index", columns={"created"})
 * })
 * @Audit\Auditable()
 * @Audit\Security(view={"ROLE_ADMIN"})
 */
class Organization
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $scan;

    /**
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="organizations")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity="Letter", mappedBy="organization")
     */
    private $letters;

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

    public function __construct()
    {
        $this->letters = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }


    public function toArray()
    {
        $arr = [
            'id' => $this->getId(),
            'text' => $this->getName().' '.($this->getScan()?'📷':'🔒'),
            'name' => $this->getName(),
            'scan' => $this->getScan(),

        ];
        if ($this->getLocation()) {
            $arr['locationId'] =$this->getLocation()->getId();
            $arr['locationName'] =$this->getLocation()->getName();
        } else {
            $arr['locationId'] = null;
            $arr['locationName'] = '';
        }
        return $arr;
    }

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

    public function getScan(): ?bool
    {
        return $this->scan;
    }

    public function setScan(bool $scan): self
    {
        $this->scan = $scan;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

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

    /**
     * @return Collection|Letter[]
     */
    public function getLetters(): Collection
    {
        return $this->letters;
    }

    public function addLetter(Letter $letter): self
    {
        if (!$this->letters->contains($letter)) {
            $this->letters[] = $letter;
            $letter->setOrganization($this);
        }

        return $this;
    }

    public function removeLetter(Letter $letter): self
    {
        if ($this->letters->removeElement($letter)) {
            // set the owning side to null (unless already changed)
            if ($letter->getOrganization() === $this) {
                $letter->setOrganization(null);
            }
        }

        return $this;
    }
}
