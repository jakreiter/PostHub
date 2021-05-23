<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use DH\Auditor\Provider\Doctrine\Auditing\Annotation as Audit;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 * @Audit\Auditable()
 * @Audit\Security(view={"ROLE_ADMIN"})
 */
class User implements UserInterface, \Serializable
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const ALL_ROLES = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Audit\Ignore
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=127, unique=false, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=127, unique=false, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $newEmail;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $newEmailToken;


    /**
     * @ORM\Column(type="boolean", options={"default":0}, nullable=false)
     */
    private $enabled = false;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="Organization", mappedBy="owner")
     */
    private $organizations;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastPassResetRequest;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $passResetToken;




    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $ipAddressDuringRegistration;


    public function __construct()
    {
        $this->enabled = false;
        $this->organizations = new ArrayCollection();
    }

    public function toArray()
    {
        $arr = [
            'id' => $this->getId(),
            'text' => $this->getUsername().' ('.$this->getEmail().')',
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'roles' => $this->getRoles()
        ];
        return $arr;
    }

    public function __toString()
    {
        $niceName = '';
        if ($this->getFirstName()) $niceName .= $this->getFirstName();
        if ($this->getLastName()) $niceName .= ' ' . $this->getLastName();
        if ($niceName) {
            $niceName .= ' (' . $this->getUsername() . ')';
            return $niceName;
        }
        return $this->getUsername();
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }


    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->enabled
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->enabled
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }


    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {

        $roles = $this->roles;


        //$roles = array_values($roles);
        return $roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Checks if the user has the selected role
     * @param $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function getRolesAsCommaSeparatedString()
    {
        $rolesArr = $this->getRoles();
        return implode(',', $rolesArr);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNewEmail(): ?string
    {
        return $this->newEmail;
    }

    public function setNewEmail(?string $newEmail): self
    {
        $this->newEmail = $newEmail;

        return $this;
    }

    public function getNewEmailToken(): ?string
    {
        return $this->newEmailToken;
    }

    public function setNewEmailToken(?string $newEmailToken): self
    {
        $this->newEmailToken = $newEmailToken;

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

    public function getLastPassResetRequest(): ?\DateTimeInterface
    {
        return $this->lastPassResetRequest;
    }

    public function setLastPassResetRequest(?\DateTimeInterface $lastPassResetRequest): self
    {
        $this->lastPassResetRequest = $lastPassResetRequest;

        return $this;
    }

    public function getPassResetToken(): ?string
    {
        return $this->passResetToken;
    }

    public function setPassResetToken(?string $passResetToken): self
    {
        $this->passResetToken = $passResetToken;

        return $this;
    }

    public function getIpAddressDuringRegistration(): ?string
    {
        return $this->ipAddressDuringRegistration;
    }

    public function setIpAddressDuringRegistration(?string $ipAddressDuringRegistration): self
    {
        $this->ipAddressDuringRegistration = $ipAddressDuringRegistration;

        return $this;
    }

    /**
     * @return Collection|Organization[]
     */
    public function getOrganizations(): Collection
    {
        return $this->organizations;
    }

    public function addOrganization(Organization $organization): self
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations[] = $organization;
            $organization->setOwner($this);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): self
    {
        if ($this->organizations->removeElement($organization)) {
            // set the owning side to null (unless already changed)
            if ($organization->getOwner() === $this) {
                $organization->setOwner(null);
            }
        }

        return $this;
    }




}
