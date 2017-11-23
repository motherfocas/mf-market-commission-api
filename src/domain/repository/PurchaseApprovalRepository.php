<?php

namespace domain\repository;

interface PurchaseApprovalRepository
{
    public function findByPurchase(int $purchaseId): array;
    public function changeStatus(int $purchaseId, int $userId, bool $status);
}