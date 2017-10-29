<?php

namespace domain\repository;

use domain\entity\Purchase;

interface PurchaseRepository
{
    function find(): array;
    function findById(int $id);
    function save(Purchase $purchase): Purchase;
    function update(Purchase $purchase): Purchase;
    function delete(int $id);
}
