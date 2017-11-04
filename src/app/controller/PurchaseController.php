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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PurchaseController implements ControllerProviderInterface
{
    public function connect(Application $app): ControllerCollection
    {
        /** @var ControllerCollection $factory */
        $factory = $app['controllers_factory'];
        $factory->get('', 'app\controller\PurchaseController::list');
        $factory->get('/{id}/', 'app\controller\PurchaseController::findById');
        $factory->post('', 'app\controller\PurchaseController::save');
        $factory->patch('/{id}/', 'app\controller\PurchaseController::update');
        $factory->delete('/{id}/', 'app\controller\PurchaseController::delete');

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
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $app['security.token_storage'];
        $response = null;

        try {
            /** @var Purchase $purchase */
            $purchase = $serializer->deserialize($request->getContent(), Purchase::class, 'json');
            $purchase->setUser($tokenStorage->getToken()->getUser());
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
                $serializer->serialize(new Message('Cannot save purchase'), 'json'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $response;
    }

    public function update(Application $app, $id, Request $request)
    {
        /** @var SerializerInterface $serializer */
        $serializer = $app['serializer'];
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $app['security.token_storage'];
        $response = null;

        try {
            /** @var Purchase $dbPurchase */
            $dbPurchase = $app['usecase.purchase.find_by_id']->execute($id);
            $this->checkPermission($dbPurchase, $tokenStorage->getToken()->getUser());
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
                $serializer->serialize(new Message('Cannot update purchase'), 'json'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $response;
    }

    public function delete(Application $app, $id)
    {
        /** @var SerializerInterface $serializer */
        $serializer = $app['serializer'];
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $app['security.token_storage'];
        $response = null;

        try {
            /** @var Purchase $dbPurchase */
            $dbPurchase = $app['usecase.purchase.find_by_id']->execute($id);
            $this->checkPermission($dbPurchase, $tokenStorage->getToken()->getUser());
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
                $serializer->serialize(new Message('Cannot delete purchase'), 'json'),
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
}
