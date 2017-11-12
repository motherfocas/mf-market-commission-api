<?php

namespace infrastructure\repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use domain\entity\User;
use domain\repository\UserRepository;
use Exception;
use infrastructure\exception\EntityNotFoundException;
use infrastructure\exception\EntityNotSavedException;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DoctrineUserRepository implements UserProviderInterface, UserRepository, UserRepositoryInterface
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityRepository $entityRepository, EntityManagerInterface $entityManager)
    {
        $this->entityRepository = $entityRepository;
        $this->entityManager = $entityManager;
    }

    public function loadUserByUsername($username)
    {
        /** @var UserInterface $user */
        $user = $this->entityRepository->findOneBy(['username' => $username]);

        if($user === null) {
            throw new UsernameNotFoundException(sprintf('User with username "%s" not found', $username));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if(!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'domain\entity\User';
    }

    /**
     * @param User $user
     * @return User
     * @throws EntityNotSavedException
     */
    public function save(User $user): User
    {
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        catch(Exception $exception) {
            throw new EntityNotSavedException('Cannot save user');
        }

        return $user;
    }

    /**
     * Get a user entity.
     *
     * @param string $username
     * @param string $password
     * @param string $grantType The grant type used
     * @param ClientEntityInterface $clientEntity
     *
     * @return UserEntityInterface
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    )
    {
        if($grantType === 'password') {
            try {
                return $this->findByCredentials($username, $password);
            }
            catch(EntityNotFoundException $exception) {
                return null;
            }
        }

        return null;
    }

    /**
     * @param string $username
     * @param string $password
     * @return User|null|
     * @throws EntityNotFoundException
     */
    private function findByCredentials(string $username, string $password)
    {
        /** @var User $user */
        $user = $this->entityRepository->findOneBy([
            'username' => $username,
            'password' => $password
        ]);

        if($user === null) {
            throw new EntityNotFoundException('User not found');
        }

        return $user;
    }
}
