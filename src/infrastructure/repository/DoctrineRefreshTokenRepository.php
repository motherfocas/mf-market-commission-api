<?php

namespace infrastructure\repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use domain\entity\oauth\RefreshToken;
use Exception;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class DoctrineRefreshTokenRepository implements RefreshTokenRepositoryInterface
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

    /**
     * Creates a new refresh token
     *
     * @return RefreshTokenEntityInterface
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    /**
     * Create a new refresh token_name.
     *
     * @param RefreshTokenEntityInterface $refreshTokenEntity
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws Exception
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        if($this->findById($refreshTokenEntity->getIdentifier()) != null) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        try {
            $this->entityManager->persist($refreshTokenEntity);
            $this->entityManager->flush();
        }
        catch(Exception $exception) {
            throw new Exception('Cannot save access token with id ' . $refreshTokenEntity->getIdentifier());
        }
    }

    /**
     * Revoke the refresh token.
     *
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {
        $refreshToken = $this->findById($tokenId);

        if($refreshToken != null) {
            $this->entityManager->remove($refreshToken);
            $this->entityManager->flush();
        }
    }

    /**
     * Check if the refresh token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        if($this->findById($tokenId) === null) {
            return true;
        }

        return false;
    }

    private function findById(string $id)
    {
        return $this->entityRepository->findOneBy(['id' => $id]);
    }
}
