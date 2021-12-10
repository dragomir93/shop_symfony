<?php

namespace App\Services;

use Symfony\Component\Security\Core\Security;

class UserService {

    public function getUser(Security $security): int 
    {
        $user = $security->getUser();

        if ($user == null) {
            $user_id = 0;
        } else {
            $user_id = $user->getId();
            
        }

        return $user_id;
    }
}