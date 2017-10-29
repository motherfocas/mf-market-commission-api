<?php

namespace domain\usecase;

use domain\entity\Purchase;
use domain\repository\PurchaseRepository;

class PurchaseFindUseCase
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
     * @return Purchase[]
     */
    public function execute(): array {
        return $this->repository->find();
    }
}
