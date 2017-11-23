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


    public function changeStatus(int $purchaseId, int $userId, bool $status)
    {
        /** @var PurchaseApproval $status */
        $dbStatus = $this->entityRepository->findOneBy(['purchase' => $purchaseId, 'user' => $userId]);

        if($dbStatus === null) {
            $dbStatus = new PurchaseApproval(
                $this->entityManager->getReference('domain\entity\Purchase', $purchaseId),
                $this->entityManager->getReference('domain\entity\User', $userId),
                $status
            );
        }
        else {
            $dbStatus->setApproved($status);
        }

        $this->entityManager->persist($dbStatus);
        $this->entityManager->flush();
    }
}
