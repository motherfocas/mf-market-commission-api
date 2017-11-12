<?php

namespace domain\entity;

use League\OAuth2\Server\Entities\UserEntityInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class User implements UserInterface, UserEntityInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $salt;

    /**
     * @var string
     *
     * @Assert/Length(max=4096)
     */
    private $plainPassword;

    /**
     * @var string
     */
    private $roles;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    public function getSalt(): ?string
    {
        return hex2bin($this->salt);
    }

    public function setSalt(string $salt): User
    {
        $this->salt = bin2hex($salt);
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getRoles(): array
    {
        return explode(',', $this->roles);
    }

    public function setRoles(array $roles): User
    {
        $this->roles = implode(',', $roles);
        return $this;
    }

    public function eraseCredentials() {}

    /**
     * Return the user's identifier.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->id;
    }
}
