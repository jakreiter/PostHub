<?php
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;


class PassSetter
{

    #[Assert\NotBlank]
    private $newPassword;

    #[Assert\NotBlank]
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
