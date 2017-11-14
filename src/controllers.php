<?php

use app\controller\AccessTokenController;
use app\controller\PurchaseController;
use app\controller\UserController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @var \Silex\Application $app */
$app->mount('/purchase/', new PurchaseController());
$app->mount('/user/', new UserController());
$app->mount('/accesstoken/', new AccessTokenController());

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if($app['debug']) {
        return;
    }

    return new Response(null, $code);
});
