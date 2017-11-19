<?php

namespace app\middleware;

use domain\entity\Message;
use domain\response\JsonResponse;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Silex\Application;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * @var ResourceServer
     */
    private $server;

    /**
     * @param ResourceServer $server
     */
    public function __construct(ResourceServer $server)
    {
        $this->server = $server;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return JsonResponse|bool
     */
    public function invoke(Application $app, Request $request)
    {
        /** @var DiactorosFactory $diactorosFactory */
        $diactorosFactory = $app['diactoros.factory'];
        $psrRequest = $diactorosFactory->createRequest($request);

        try {
            $this->server->validateAuthenticatedRequest($psrRequest);
            return true;
        }
        catch(OAuthServerException $exception) {
            return new JsonResponse(new Message($exception->getMessage()), $exception->getHttpStatusCode());
        }
        catch(\Exception $exception) {
            return new JsonResponse(new Message($exception->getMessage()), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
