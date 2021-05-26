<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


class PassSetter
{


    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank()
     */
    private $newPassword;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank()
     */
    private $newPassword2;



    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getNewPassword2(): ?string
    {
        return $this->newPassword2;
    }

    public function setNewPassword2(string $newPassword): self
    {
        $this->newPassword2 = $newPassword;

        return $this;
    }




}
