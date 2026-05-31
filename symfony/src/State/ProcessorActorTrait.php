<?php

namespace App\State;

use App\Entity\ConnectionUser;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait ProcessorActorTrait
{
    protected function getCurrentConnectionUser(): ConnectionUser
    {
        $user = $this->security->getUser();

        if (!$user instanceof ConnectionUser) {
            throw new AccessDeniedException('Authentication required.');
        }

        return $user;
    }
}
