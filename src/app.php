<?php

use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\ORM\EntityManager;
use domain\entity\Purchase;
use domain\entity\User;
use domain\mapper\JsonToUserMapper;
use domain\usecase\PurchaseDeleteUseCase;
use domain\usecase\PurchaseFindByIdUseCase;
use domain\usecase\PurchaseFindUseCase;
use domain\usecase\PurchaseSaveUseCase;
use domain\usecase\PurchaseUpdateUseCase;
use domain\usecase\UserSaveUseCase;
use infrastructure\repository\DoctrinePurchaseRepository;
use infrastructure\repository\DoctrineUserRepository;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
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
                'namespace' => 'domain\entity',
                'path' => __DIR__.'/domain/resources/config/doctrine',
            ],
        ],
    ],
]);

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});
$app['debug'] = false;

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
// Late registers
//
$app->register(new Silex\Provider\SecurityServiceProvider(), [
    'security.firewalls' => [
        'purchase' => [
            'pattern' => '^/purchase',
            'http' => true,
            'users' => $app['repository.user']
        ]
    ]
]);
$app['security.encoder_factory'] = function($app) {
    return new EncoderFactory(array(
        'domain\entity\User' => $app['security.encoder.digest'],
    ));
};

return $app;
