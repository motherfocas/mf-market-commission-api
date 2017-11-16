<?php

namespace app\controller;

use domain\entity\Message;
use domain\helper\HttpFoundationHelper;
use domain\response\JsonResponse;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        /** @var ControllerCollection $factory */
        $factory = $app['controllers_factory'];
        $factory->post('', 'app\controller\AccessTokenController::request');

        return $factory;
    }

    public function request(Application $app, Request $request)
    {
        /** @var AuthorizationServer $authServer */
        $authServer = $app['auth.server'];
        /** @var DiactorosFactory $diactorosFactory */
        $diactorosFactory = $app['diactoros.factory'];
        /** @var HttpFoundationFactory $httpFoundationFactory */
        $httpFoundationFactory = $app['http.foundation.factory'];
        /** @var HttpFoundationHelper $httpFoundationHelper */
        $httpFoundationHelper = $app['http.foundation.helper'];
        /** @var Response $response */
        $response = null;

        try {
            $psrResponse = $diactorosFactory->createResponse(new Response());
            $httpFoundationHelper->fillRequestFromJson($request);
            $authServer->respondToAccessTokenRequest(
                $diactorosFactory->createRequest($request),
                $psrResponse
            );

            $response = new JsonResponse($psrResponse->getBody(), $psrResponse->getStatusCode());
        }
        catch(OAuthServerException $exception) {
            $response = new JsonResponse(new Message($exception->getMessage()), $exception->getHttpStatusCode());
        }
        catch(Exception $exception) {
            $response = new JsonResponse(new Message($exception->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }
}
