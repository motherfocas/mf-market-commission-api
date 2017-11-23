<?php

namespace domain\usecase;

use domain\entity\Purchase;
use domain\entity\User;
use domain\exception\CannotApproveOwnPurchaseException;
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
        if($purchase->getUser()->getId() === $user->getId()) {
            throw new CannotApproveOwnPurchaseException('Cannot approve or reject of your own purchase');
        }

        $this->repository->changeStatus($purchase->getId(), $user->getId(), $status);
    }
}
