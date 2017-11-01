<?php

namespace domain\repository;

use domain\entity\Purchase;

interface PurchaseRepository
{
    public function find(): array;
    public function findById(int $id);
    public function save(Purchase $purchase): Purchase;
    public function update(Purchase $purchase): Purchase;
    public function delete(int $id);
}
