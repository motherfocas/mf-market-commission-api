<?php

namespace domain\entity;

class Item
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
     * @var float
     */
    private $price;

    public function __construct(int $id, string $name, float $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrice(): float
    {
        return number_format($this->price, 2);
    }

    public function update(Item $item)
    {
        if(isset($item->name)) $this->name = $item->name;
        if(isset($item->price)) $this->price = $item->price;
    }
}
