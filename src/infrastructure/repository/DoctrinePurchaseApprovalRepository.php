<?php

namespace infrastructure\repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use domain\entity\PurchaseApproval;
use domain\repository\PurchaseApprovalRepository;

class DoctrinePurchaseApprovalRepository implements PurchaseApprovalRepository
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityRepository $entityRepository, EntityManagerInterface $entityManager)
    {
        $this->entityRepository = $entityRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $purchaseId
     * @return PurchaseApproval[]|array
     */
    public function findByPurchase(int $purchaseId): array
    {
        /** @var PurchaseApproval $status */
        $purchaseApprovals = $this->entityRepository->findBy(['purchase' => $purchaseId]);

        if($purchaseApprovals === null) {
            return [];
        }

        return $purchaseApprovals;
    }

    public function changeStatus(int $purchaseId, int $userId, bool $status)
    {
        /** @var PurchaseApproval $status */
        $purchaseApproval = $this->entityRepository->findOneBy(['purchase' => $purchaseId, 'user' => $userId]);

        if($purchaseApproval === null) {
            $purchaseApproval = new PurchaseApproval(
                $this->entityManager->getReference('domain\entity\Purchase', $purchaseId),
                $this->entityManager->getReference('domain\entity\User', $userId),
                $status
            );
        }
        else {
            $purchaseApproval->setApproved($status);
        }

        $this->entityManager->persist($purchaseApproval);
        $this->entityManager->flush();
    }
}
