<?php

namespace domain\usecase;

use domain\entity\Purchase;
use domain\entity\User;
use domain\repository\PurchaseApprovalRepository;

class PurchaseChangeStatusUseCase
{
    /**
     * @var PurchaseApprovalRepository
     */
    private $repository;

    public function __construct(PurchaseApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(Purchase $purchase, User $user, bool $status) {
        $this->repository->changeStatus($purchase->getId(), $user->getId(), $status);
    }
}
