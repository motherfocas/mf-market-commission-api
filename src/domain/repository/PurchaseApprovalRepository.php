<?php

namespace domain\repository;

interface PurchaseApprovalRepository
{
    public function changeStatus(int $purchaseId, int $userId, bool $status);
}