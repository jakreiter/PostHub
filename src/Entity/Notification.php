<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DH\Auditor\Provider\Doctrine\Auditing\Annotation as Audit;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 * @Audit\Auditable()
 * @Audit\Security(view={"ROLE_ADMIN"})
 * @Assert\EnableAutoMapping()
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    private $sentMessageId;


    /**
     * @ORM\OneToMany(targetEntity="Letter", mappedBy="notification")
     */
    private $letters;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $recipient;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $contents;


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
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->letters = new ArrayCollection();


    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSentMessageId(): ?string
    {
        return $this->sentMessageId;
    }

    public function setSentMessageId(?string $sentMessageId): self
    {
        $this->sentMessageId = $sentMessageId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(?string $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getContents(): ?string
    {
        return $this->contents;
    }

    public function setContents(?string $contents): self
    {
        $this->contents = $contents;

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
            $letter->setNotification($this);
        }

        return $this;
    }

    public function removeLetter(Letter $letter): self
    {
        if ($this->letters->removeElement($letter)) {
            // set the owning side to null (unless already changed)
            if ($letter->getNotification() === $this) {
                $letter->setNotification(null);
            }
        }

        return $this;
    }


}
