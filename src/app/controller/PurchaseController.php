<?php

namespace app\controller;

use DateTime;
use domain\entity\Message;
use domain\entity\Purchase;
use domain\entity\Purchases;
use domain\entity\User;
use domain\exception\NotAuthorizedException;
use domain\response\JsonResponse;
use Exception;
use infrastructure\exception\EntityNotDeletedException;
use infrastructure\exception\EntityNotFoundException;
use infrastructure\exception\EntityNotSavedException;
use JMS\Serializer\SerializerInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PurchaseController implements ControllerProviderInterface
{
    private $authMiddleware;

    public function __construct()
    {
        $this->authMiddleware = function(Request $request, Application $app) {
            $authResult = $app['auth.middleware']->invoke($app, $request);

            if($authResult !== true) {
                return $authResult;
            }
        };
    }

    public function connect(Application $app): ControllerCollection
    {
        /** @var ControllerCollection $factory */
        $factory = $app['controllers_factory'];
        $factory->get('', 'app\controller\PurchaseController::list')->before($this->authMiddleware);
        $factory->get('/{id}/', 'app\controller\PurchaseController::findById')->before($this->authMiddleware);
        $factory->post('', 'app\controller\PurchaseController::save')->before($this->authMiddleware);
        $factory->patch('/{id}/', 'app\controller\PurchaseController::update')->before($this->authMiddleware);
        $factory->delete('/{id}/', 'app\controller\PurchaseController::delete')->before($this->authMiddleware);

        return $factory;
    }

    public function list(Application $app)
    {
        /** @var SerializerInterface $serializer */
        $serializer = $app['serializer'];

        return new JsonResponse(
            $serializer->serialize(
                new Purchases($app['usecase.purchase.find']->execute()),
                'json'
            )
        );
    }

    public function findById(Application $app, $id)
    {
        /** @var SerializerInterface $serializer */
        $serializer = $app['serializer'];
        $response = null;

        try {
            $response = new JsonResponse(
                $serializer->serialize(new Purchases([$app['usecase.purchase.find_by_id']->execute($id)]),
                    'json')
            );
        }
        catch(EntityNotFoundException $exception) {
            $response = new JsonResponse(new Message($exception->getMessage()), Response::HTTP_NOT_FOUND);
        }

        return $response;
    }

    public function save(Application $app, Request $request)
    {
        /** @var SerializerInterface $serializer */
        $serializer = $app['serializer'];
        $user = $app['usecase.user.find_by_id']->execute($this->getUserId($request));
        $response = null;

        try {
            /** @var Purchase $purchase */
            $purchase = $serializer->deserialize($request->getContent(), Purchase::class, 'json');
            $purchase->setUser($user);
            $purchase->setDate(new DateTime());
            $response = new JsonResponse(
                $serializer->serialize($app['usecase.purchase.save']->execute($purchase), 'json'),
                Response::HTTP_CREATED
            );
        }
        catch(EntityNotSavedException $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message($exception->getMessage()), 'json'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        catch(Exception $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message('Cannot save purchase: ' . $exception->getMessage()), 'json'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $response;
    }

    public function update(Application $app, $id, Request $request)
    {
        /** @var SerializerInterface $serializer */
        $serializer = $app['serializer'];
        $user = $app['usecase.user.find_by_id']->execute($this->getUserId($request));
        $response = null;

        try {
            /** @var Purchase $dbPurchase */
            $dbPurchase = $app['usecase.purchase.find_by_id']->execute($id);
            $this->checkPermission($dbPurchase, $user);
            /** @var Purchase $purchase */
            $purchase = $serializer->deserialize($request->getContent(), Purchase::class, 'json');
            $purchase->setId($id);
            $response = new JsonResponse(
                $serializer->serialize($app['usecase.purchase.update']->execute($purchase), 'json'),
                Response::HTTP_OK
            );
        }
        catch(NotAuthorizedException $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message($exception->getMessage()), 'json'),
                Response::HTTP_UNAUTHORIZED
            );
        }
        catch(EntityNotSavedException $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message($exception->getMessage()), 'json'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        catch(Exception $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message('Cannot update purchase: ' . $exception->getMessage()), 'json'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $response;
    }

    public function delete(Application $app, $id, Request $request)
    {
        /** @var SerializerInterface $serializer */
        $serializer = $app['serializer'];
        $user = $app['usecase.user.find_by_id']->execute($this->getUserId($request));
        $response = null;

        try {
            /** @var Purchase $dbPurchase */
            $dbPurchase = $app['usecase.purchase.find_by_id']->execute($id);
            $this->checkPermission($dbPurchase, $user);
            $app['usecase.purchase.delete']->execute($id);
            $response = new JsonResponse(null, Response::HTTP_OK);
        }
        catch(NotAuthorizedException $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message($exception->getMessage()), 'json'),
                Response::HTTP_UNAUTHORIZED
            );
        }
        catch(EntityNotFoundException $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message($exception->getMessage()), 'json'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        catch(EntityNotDeletedException $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message($exception->getMessage()), 'json'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        catch(Exception $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message('Cannot delete purchase: ' . $exception->getMessage()), 'json'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $response;
    }

    /**
     * @param Purchase $purchase
     * @param User $user
     * @throws NotAuthorizedException
     */
    private function checkPermission(Purchase $purchase, User $user)
    {
        if(!$purchase->isAuthorized($user)) {
            throw new NotAuthorizedException('You cannot modify or delete other user\'s purchases');
        }
    }

    private function getUserId(Request $request)
    {
        return $request->attributes->get('user_id');
    }
}
