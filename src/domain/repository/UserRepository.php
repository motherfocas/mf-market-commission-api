<?php

namespace domain\repository;

use domain\entity\User;

interface UserRepository
{
    public function findById(int $id): User;
    public function save(User $user): User;
}
