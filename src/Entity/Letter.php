<?php

namespace App\Entity;

use App\Repository\LetterRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use DH\Auditor\Provider\Doctrine\Auditing\Annotation as Audit;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=LetterRepository::class)
 * @ORM\Table(uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_barcode_number", columns={"barcode_number"})
 * }, indexes={
 *      @ORM\Index(name="seen_in_organization_index", columns={"organization_id", "seen"}),
 *      @ORM\Index(name="seen_in_organization_index", columns={"organization_id", "seen"}),
 *      @ORM\Index(name="downloaded_by_user_index", columns={"downloaded_by_user_id"}),
 *      @ORM\Index(name="created_index", columns={"created"})
 * })
 * @Audit\Auditable()
 * @Audit\Security(view={"ROLE_ADMIN"})
 * @Assert\EnableAutoMapping()
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
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $organization;

    /**
     * @ORM\ManyToOne(targetEntity="LetterStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
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
     * @ORM\ManyToOne(targetEntity="Notification", inversedBy="letters")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $notification;


    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     */
    private $notificationSent=false;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":false})
     */
    private $seen=false;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $downloadedByUser;

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

    /**
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastAttemptToSendNotification;

    /**
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $scanOrdered;

    /**
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $scanInserted;

    /**
     *
     * @ORM\Column(type="float", nullable=false, options={"default":0})
     */
    private $scanDue=0.0;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $createdByUser;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $modifiedByUser;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->scanDue = 0.0;
        $this->scanOrdered = null;

    }

    public function __toString()
    {
        return $this->getTitle();
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

    public function getId()
    {
        return $this->id;
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

    public function getCreatedByUser(): ?User
    {
        return $this->createdByUser;
    }

    public function setCreatedByUser(?User $createdByUser): self
    {
        $this->createdByUser = $createdByUser;

        return $this;
    }

    public function getModifiedByUser(): ?User
    {
        return $this->modifiedByUser;
    }

    public function setModifiedByUser(?User $modifiedByUser): self
    {
        $this->modifiedByUser = $modifiedByUser;

        return $this;
    }

    public function getSeen(): ?bool
    {
        return $this->seen;
    }

    public function setSeen(bool $seen): self
    {
        $this->seen = $seen;

        return $this;
    }

    public function getDownloadedByUser(): ?User
    {
        return $this->downloadedByUser;
    }

    public function setDownloadedByUser(?User $downloadedByUser): self
    {
        $this->downloadedByUser = $downloadedByUser;

        return $this;
    }

    public function getScanOrdered(): ?\DateTimeInterface
    {
        if (!$this->scanOrdered) return null;
        return $this->scanOrdered;
    }

    public function setScanOrdered(?\DateTimeInterface $scanOrdered): self
    {

        $this->scanOrdered = $scanOrdered;

        return $this;
    }

    public function getScanInserted(): ?\DateTimeInterface
    {
        return $this->scanInserted;
    }

    public function setScanInserted(?\DateTimeInterface $scanInserted): self
    {
        $this->scanInserted = $scanInserted;

        return $this;
    }

    public function getScanDue(): ?float
    {
        return $this->scanDue;
    }

    public function setScanDue(float $scanDue): self
    {
        $this->scanDue = $scanDue;

        return $this;
    }

    public function getNotificationSent(): ?bool
    {
        return $this->notificationSent;
    }

    public function setNotificationSent(bool $notificationSent): self
    {
        $this->notificationSent = $notificationSent;

        return $this;
    }

    public function getLastAttemptToSendNotification(): ?\DateTimeInterface
    {
        return $this->lastAttemptToSendNotification;
    }

    public function setLastAttemptToSendNotification(?\DateTimeInterface $lastAttemptToSendNotification): self
    {
        $this->lastAttemptToSendNotification = $lastAttemptToSendNotification;

        return $this;
    }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

}
