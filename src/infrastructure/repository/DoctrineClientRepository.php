<?php

namespace infrastructure\repository;

use Doctrine\ORM\EntityRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class DoctrineClientRepository implements ClientRepositoryInterface
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     * @param string $grantType The grant type used
     * @param null|string $clientSecret The client's secret (if sent)
     * @param bool $mustValidateSecret If true the client must attempt to validate the secret if the client
     *                                        is confidential
     *
     * @return ClientEntityInterface|null
     */
    public function getClientEntity($clientIdentifier, $grantType, $clientSecret = null, $mustValidateSecret = true)
    {
        /** @var ClientEntityInterface|null $entity */
        $entity = $this->entityRepository->findOneBy([
            'identifier' => $clientIdentifier,
            'grantType' => $grantType
        ]);

        return $entity;
    }
}
