<?php

namespace domain\usecase;

use domain\entity\Purchase;
use domain\entity\User;
use domain\repository\PurchaseRepository;
use domain\repository\UserRepository;
use infrastructure\exception\EntityNotSavedException;

class UserSaveUseCase
{
    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(User $user): User
    {
        return $this->repository->save($user);
    }
}
