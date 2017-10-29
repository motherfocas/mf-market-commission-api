<?php

namespace domain\mapper;

use domain\entity\User;
use domain\exception\MapperException;
use JMS\Serializer\SerializerInterface;

class JsonToUserMapper
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function map(string $json): User
    {
        /** @var User $user */
        $user = $this->serializer->deserialize($json, User::class, 'json');

        if($user->getUsername() === null || $user->getPlainPassword() === null) {
            throw new MapperException('Username and plain password are required fields');
        }

        return $user;
    }
}
