<?php

namespace domain\repository;

use domain\entity\User;

interface UserRepository
{
    function save(User $user): User;
}
