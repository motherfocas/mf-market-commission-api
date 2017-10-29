<?php

namespace domain\usecase;

use domain\entity\Purchase;
use domain\repository\PurchaseRepository;
use infrastructure\exception\EntityNotFoundException;

class PurchaseFindByIdUseCase
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
     * @return Purchase
     * @throws EntityNotFoundException
     */
    public function execute(int $id) {
        return $this->repository->findById($id);
    }
}
