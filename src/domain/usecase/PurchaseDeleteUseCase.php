<?php

namespace domain\usecase;

use domain\entity\Purchase;
use domain\repository\PurchaseRepository;
use infrastructure\exception\EntityNotDeletedException;

class PurchaseDeleteUseCase
{
    /**
     * @var PurchaseRepository
     */
    private $repository;

    public function __construct(PurchaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $id
     * @throws EntityNotDeletedException
     */
    public function execute(int $id) {
        $this->repository->delete($id);
    }
}
