<?php

namespace domain\entity;

use DateTime;

class Purchase
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Item[]
     */
    private $items;

    /**
     * @var User
     */
    private $user;

    /**
     * @var DateTime
     */
    private $date;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Purchase
    {
        $this->id = $id;
        return $this;
    }

    public function setUser(User $user): Purchase
    {
        $this->user = $user;
        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): Purchase
    {
        $this->date = $date;
        return $this;
    }

    public function update(Purchase $purchase)
    {
        if(isset($purchase->name)) $this->name = $purchase->name;
        $updatedItems = [];

        foreach($purchase->items as $item) {
            $foundItem = $this->findItem($item);

            if($foundItem !== null) {
                $foundItem->update($item);
                array_push($updatedItems, $foundItem);
            }
        }

        $this->items = $updatedItems;
    }

    public function getTotalPrice(): float
    {
        $total = 0;

        foreach($this->items as $item) {
            $total += $item->getPrice();
        }

        return $total;
    }

    public function getTotalItems(): int
    {
        return count($this->items);
    }

    public function isAuthorized(User $user): bool
    {
        return $user->getId() === $this->user->getId();
    }

        /**
     * @param Item $itemToFind
     * @return Item|null
     */
    private function findItem(Item $itemToFind)
    {
        foreach($this->items as $item) {
            if($item->getId() === $itemToFind->getId()) {
                return $item;
            }
        }

        return null;
    }
}
