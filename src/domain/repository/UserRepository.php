<?php

namespace domain\repository;

use domain\entity\User;

interface UserRepository
{
    public function save(User $user): User;
}
