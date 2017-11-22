<?php

namespace domain\usecase;

use domain\entity\User;
use domain\repository\UserRepository;

class UserFindByIdUseCase
{
    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): User
    {
        return $this->repository->findById($id);
    }
}
