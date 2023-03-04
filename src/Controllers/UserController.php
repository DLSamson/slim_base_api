<?php

namespace Api\Controllers;

use Api\Core\Http\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Models\User;
use Slim\Routing\Route;
use Slim\Routing\RouteCollector;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;

class UserController extends BaseController {
    public function register(Request $request, Response $response) {
        $json = $request->getBody()->getContents();
        $data = json_decode($json);

        $violations = $this->validator->validate((array) $data, new Assert\Collection([
            'firstName' => new Assert\NotBlank(),
            'lastName' => new Assert\NotBlank(),
            'email' => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'password' => new Assert\NotBlank(),
        ]));

        /* If user is Authenticated */
        if(User::Auth($request->getHeaderLine('Authorization'))) {
            $response->getBody()->write('Запрос от авторизованного аккаунта');
            return $response->withStatus(403);
        }

        /* No errors, trying creating user */
        if($violations->count() === 0) {
            if(User::where(['email' => $data->email])->first()) {
                $response->getBody()->write('Аккаунт с таким email уже существует');
                return $response->withStatus(409);
            }

            $user = new User([
                'firstName' => $data->firstName,
                'lastName' => $data->lastName,
                'email' => $data->email,
                'passwordHash' => User::HashPassword($data->password),
            ]);

            if($user->save()) {
                $this->log->info("New user created", [
                    'email' => $data->email
                ]);
                $response->getBody()->write(json_encode([
                    'id' => $user->id,
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'email' => $user->email,
                ]));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(201);
            }

            return $response->withStatus(500);
        }

        /* If has errors */
        $errors = [];
        foreach($violations as $violation) {
            /* @var ConstraintViolationInterface $violation */
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        $response->getBody()->write(json_encode($errors, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    public function
}