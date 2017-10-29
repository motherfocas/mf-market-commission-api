<?php

namespace domain\entity;

class Purchases
{
    /**
     * @var Purchase[]
     */
    private $purchases;

    /**
     * @param Purchase[] $purchases
     */
    public function __construct(array $purchases)
    {
        $this->purchases = $purchases;
    }
}
