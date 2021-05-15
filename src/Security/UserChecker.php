<?php

namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException('The account is not active.');
        }

/*        if (!$user->hasRole('ROLE_USER')) {
            throw new CustomUserMessageAccountStatusException('Your do not have a role that allows to log in.');
        }*/
    }

    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException('The account is not active.');
        }
/*
        if (!$user->hasRole('ROLE_USER')) {
            throw new CustomUserMessageAccountStatusException('Your do not have a role that allows to log in.');
        }*/
    }
}