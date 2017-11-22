<?php

namespace infrastructure\repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use domain\entity\Purchase;
use domain\repository\PurchaseRepository;
use Exception;
use infrastructure\exception\EntityNotDeletedException;
use infrastructure\exception\EntityNotFoundException;
use infrastructure\exception\EntityNotSavedException;

class DoctrinePurchaseRepository implements PurchaseRepository
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
     * @return Purchase[]
     */
    public function find(): array
    {
        return $this->entityRepository->findAll();
    }

    /**
     * @param int $id
     * @return Purchase
     * @throws EntityNotFoundException
     */
    public function findById(int $id): Purchase
    {
        /** @var Purchase $entity */
        $entity = $this->entityRepository->find($id);

        if($entity === null) {
            throw new EntityNotFoundException('Purchase ' . $id . ' not found');
        }

        return $entity;
    }

    /**
     * @param Purchase $purchase
     * @return Purchase
     * @throws EntityNotSavedException
     */
    public function save(Purchase $purchase): Purchase
    {
        try {
            $this->entityManager->persist($purchase);
            $this->entityManager->flush();
        }
        catch(Exception $exception) {
            throw new EntityNotSavedException('Cannot save purchase');
        }

        return $purchase;
    }

    /**
     * @param Purchase $purchase
     * @return Purchase
     * @throws EntityNotSavedException
     */
    public function update(Purchase $purchase): Purchase
    {
        try {
            $dbPurchase = $this->findById($purchase->getId());
            $dbPurchase->update($purchase);
            $this->entityManager->persist($dbPurchase);
            $this->entityManager->flush();
        }
        catch(EntityNotFoundException $exception) {
            throw new EntityNotSavedException($exception->getMessage());
        }
        catch(Exception $exception) {
            throw new EntityNotSavedException('Cannot update purchase');
        }

        return $dbPurchase;
    }

    /**
     * @param int $id
     * @throws EntityNotDeletedException
     */
    public function delete(int $id)
    {
        try {
            $purchase = $this->findById($id);
            $this->entityManager->remove($purchase);
            $this->entityManager->flush();
        }
        catch(EntityNotFoundException $exception) {
            throw new EntityNotDeletedException($exception->getMessage());
        }
        catch(Exception $exception) {
            throw new EntityNotDeletedException('Cannot delete purchase');
        }
    }
}
