<?php

use app\middleware\AuthMiddleware;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\ORM\EntityManager;
use domain\entity\oauth\AccessToken;
use domain\entity\oauth\Client;
use domain\entity\oauth\RefreshToken;
use domain\entity\oauth\Scope;
use domain\entity\Purchase;
use domain\entity\User;
use domain\helper\HttpFoundationHelper;
use domain\mapper\JsonToUserMapper;
use domain\usecase\PurchaseDeleteUseCase;
use domain\usecase\PurchaseFindByIdUseCase;
use domain\usecase\PurchaseFindUseCase;
use domain\usecase\PurchaseSaveUseCase;
use domain\usecase\PurchaseUpdateUseCase;
use domain\usecase\UserSaveUseCase;
use infrastructure\repository\DoctrineAccessTokenRepository;
use infrastructure\repository\DoctrineClientRepository;
use infrastructure\repository\DoctrinePurchaseRepository;
use infrastructure\repository\DoctrineRefreshTokenRepository;
use infrastructure\repository\DoctrineScopeRepository;
use infrastructure\repository\DoctrineUserRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\ResourceServer;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

$app = new Application();

//
// Providers
//
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new Rpodwika\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/parameters.yml'));
$app->register(new JDesrosiers\Silex\Provider\JmsSerializerServiceProvider(), [
    'serializer.srcDir' => __DIR__ . '/../vendor/jms/serializer/src',
    'serializer.metadataDirs' => [
        'domain\\entity' => __DIR__ . '/domain/resources/config/serializer'
    ]
]);
$app->register(new DoctrineServiceProvider, [
    'db.options' => $app['config']['database'],
]);
$app->register(new DoctrineOrmServiceProvider, [
    'orm.em.options' => [
        'mappings' => [
            [
                'type' => 'simple_yml',
                'namespace' => 'domain\entity\oauth',
                'path' => __DIR__ . '/domain/resources/config/doctrine/oauth'
            ],
            [
                'type' => 'simple_yml',
                'namespace' => 'domain\entity',
                'path' => __DIR__ . '/domain/resources/config/doctrine'
            ]
        ]
    ]
]);

//
// Repositories
//
$app['repository.purchase'] = function($app): DoctrinePurchaseRepository {
    /** @var EntityManager $entityManager */
    $entityManager = $app['orm.em'];
    return new DoctrinePurchaseRepository($entityManager->getRepository(Purchase::class), $entityManager);
};
$app['repository.user'] = function($app): DoctrineUserRepository {
    /** @var EntityManager $entityManager */
    $entityManager = $app['orm.em'];
    return new DoctrineUserRepository($entityManager->getRepository(User::class), $entityManager);
};
$app['repository.oauth.client'] = function($app): DoctrineClientRepository {
    /** @var EntityManager $entityManager */
    $entityManager = $app['orm.em'];
    return new DoctrineClientRepository($entityManager->getRepository(Client::class));
};
$app['repository.oauth.scope'] = function($app): DoctrineScopeRepository {
    /** @var EntityManager $entityManager */
    $entityManager = $app['orm.em'];
    return new DoctrineScopeRepository($entityManager->getRepository(Scope::class));
};
$app['repository.oauth.access_token'] = function($app): DoctrineAccessTokenRepository {
    /** @var EntityManager $entityManager */
    $entityManager = $app['orm.em'];
    return new DoctrineAccessTokenRepository($entityManager->getRepository(AccessToken::class), $entityManager);
};
$app['repository.oauth.refresh_token'] = function($app): DoctrineRefreshTokenRepository {
    /** @var EntityManager $entityManager */
    $entityManager = $app['orm.em'];
    return new DoctrineRefreshTokenRepository($entityManager->getRepository(RefreshToken::class), $entityManager);
};

//
// Use cases
//
$app['usecase.purchase.find'] = function($app): PurchaseFindUseCase {
    return new PurchaseFindUseCase($app['repository.purchase']);
};
$app['usecase.purchase.find_by_id'] = function($app): PurchaseFindByIdUseCase {
    return new PurchaseFindByIdUseCase($app['repository.purchase']);
};
$app['usecase.purchase.save'] = function($app): PurchaseSaveUseCase {
    return new PurchaseSaveUseCase($app['repository.purchase']);
};
$app['usecase.purchase.update'] = function($app): PurchaseUpdateUseCase {
    return new PurchaseUpdateUseCase($app['repository.purchase']);
};
$app['usecase.purchase.delete'] = function($app): PurchaseDeleteUseCase {
    return new PurchaseDeleteUseCase($app['repository.purchase']);
};
$app['usecase.user.save'] = function($app): UserSaveUseCase {
    return new UserSaveUseCase($app['repository.user']);
};

//
// Helpers
//

$app['mapper.json_to_user'] = function($app): JsonToUserMapper {
    return new JsonToUserMapper($app['serializer']);
};

//
// OAuth
//
$publicKeyPath = __DIR__ . '/../keys/public.key';
$privateKeyPath = __DIR__ . '/../keys/private.key';
$encryptionKey = file_get_contents(__DIR__ . '/../keys/encryption.key');

$authServer = new AuthorizationServer(
    $app['repository.oauth.client'],
    $app['repository.oauth.access_token'],
    $app['repository.oauth.scope'],
    $privateKeyPath,
    $encryptionKey
);
$authGrant = new PasswordGrant($app['repository.user'], $app['repository.oauth.refresh_token']);
$authGrant->setRefreshTokenTTL(new DateInterval('P1M'));            // Refresh TTL: 1 month
$authServer->enableGrantType($authGrant, new DateInterval('PT1H')); // Access TTL: 1 hour
$app['auth.server'] = $authServer;
$app['auth.middleware'] = new AuthMiddleware(
    new ResourceServer($app['repository.oauth.access_token'], $publicKeyPath)
);

//
// Others
//
$app['http.foundation.factory'] = function(): HttpFoundationFactory {
    return new HttpFoundationFactory();
};
$app['diactoros.factory'] = function(): DiactorosFactory {
    return new DiactorosFactory();
};
$app['http.foundation.helper'] = function(): HttpFoundationHelper {
    return new HttpFoundationHelper();
};

//
// Debug mode
//
$app['debug'] = false;

return $app;
