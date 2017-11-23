<?php

namespace domain\entity;

class PurchaseApproval
{
    /**
     * @var Purchase|int
     */
    private $purchase;

    /**
     * @var User|int
     */
    private $user;

    /**
     * @var bool
     */
    private $approved;

    /**
     * PurchaseApproval constructor.
     * @param Purchase|object $purchase
     * @param User|object $user
     * @param bool $approved
     */
    public function __construct($purchase, $user, bool $approved)
    {
        $this->purchase = $purchase;
        $this->user = $user;
        $this->approved = $approved;
    }

    public function setApproved(bool $approved): PurchaseApproval
    {
        $this->approved = $approved;
        return $this;
    }
}
