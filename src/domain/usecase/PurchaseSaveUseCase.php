<?php

namespace domain\usecase;

use domain\entity\Purchase;
use domain\repository\PurchaseRepository;
use infrastructure\exception\EntityNotSavedException;

class PurchaseSaveUseCase
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
     * @param Purchase $purchase
     * @return Purchase
     * @throws EntityNotSavedException
     */
    public function execute(Purchase $purchase): Purchase {
        return $this->repository->save($purchase);
    }
}
