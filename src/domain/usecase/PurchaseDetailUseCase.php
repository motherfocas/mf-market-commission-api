<?php

namespace domain\usecase;

use domain\entity\Purchase;
use domain\entity\PurchaseApproval;
use domain\repository\PurchaseApprovalRepository;
use domain\repository\PurchaseRepository;
use infrastructure\exception\EntityNotFoundException;

class PurchaseDetailUseCase
{
    /**
     * @var PurchaseRepository
     */
    private $purchaseRepository;

    /**
     * @var PurchaseApprovalRepository
     */
    private $purchaseApprovalRepository;

    public function __construct(
        PurchaseRepository $purchaseRepository,
        PurchaseApprovalRepository $purchaseApprovalRepository
    )
    {
        $this->purchaseRepository = $purchaseRepository;
        $this->purchaseApprovalRepository = $purchaseApprovalRepository;
    }

    /**
     * @param int $id
     * @return Purchase
     * @throws EntityNotFoundException
     */
    public function execute(int $id): Purchase
    {
        /** @var PurchaseApproval[] $purchaseApprovals */
        $purchaseApprovals = $this->purchaseApprovalRepository->findByPurchase($id);
        $approvals = 0;
        $rejects = 0;

        foreach($purchaseApprovals as $purchaseApproval) {
            if($purchaseApproval->isApproved()) {
                $approvals++;
            }
            elseif($purchaseApproval->isApproved() === false) {
                $rejects++;
            }
        }

        $purchase = $this->purchaseRepository->findById($id);
        $purchase->setTotalApprovals($approvals);
        $purchase->setTotalRejects($rejects);

        return $purchase;
    }
}
