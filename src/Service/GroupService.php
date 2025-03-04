<?php

namespace App\Service;

use App\Entity\Group;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class GroupService {
    public function isGroupCreator(Group $group, UserInterface $user): bool
    {
        if($user instanceof User) {
            return ($group->getCreator()->getId() === $user->getId());
        }
        return false;
    }
}

