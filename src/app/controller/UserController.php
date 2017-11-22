<?php

namespace app\controller;

use domain\entity\Message;
use domain\entity\User;
use domain\exception\MapperException;
use domain\mapper\JsonToUserMapper;
use domain\response\JsonResponse;
use Exception;
use infrastructure\exception\EntityNotSavedException;
use JMS\Serializer\SerializerInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class UserController implements ControllerProviderInterface
{
    private const SALT_LENGTH = 32;

    public function connect(Application $app)
    {
        /** @var ControllerCollection $factory */
        $factory = $app['controllers_factory'];
        $factory->post('', 'app\controller\UserController::save');

        return $factory;
    }

    public function save(Application $app, Request $request)
    {
        /** @var SerializerInterface $serializer */
        $serializer = $app['serializer'];
        /** @var JsonToUserMapper $mapper */
        $mapper = $app['mapper.json_to_user'];
        /** @var MessageDigestPasswordEncoder $encoder */
        $encoder = $app['security.encoder.digest'];
        $response = null;

        try {
            /** @var User $user */
            $user = $mapper->map($request->getContent());
            $user->setRoles(['ROLE_USER']);
            $salt = bin2hex(random_bytes(self::SALT_LENGTH));
            $user->setPassword($encoder->encodePassword($user->getPlainPassword(), $salt));
            $user->setSalt($salt);
            $app['usecase.user.save']->execute($user);
            $response = new JsonResponse(null, Response::HTTP_CREATED);
        }
        catch(MapperException $exception) {
            $response = new JsonResponse(
                $serializer->serialize(new Message($exception->getMessage()), 'json'),
                Response::HTTP_BAD_REQUEST
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
                $serializer->serialize(new Message('Cannot save user: ' . $exception->getMessage()), 'json'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $response;
    }
}
