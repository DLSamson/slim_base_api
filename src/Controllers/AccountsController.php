<?php

namespace Api\Controllers;

use Api\Core\Http\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Models\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Illuminate\Database\Eloquent\Collection;

class AccountsController extends BaseController {
    public function register(Request $request, Response $response) {
        $json = $request->getBody()->getContents();
        $data = json_decode($json);

        $violations = $this->validator->validate((array) $data, new Assert\Collection([
            'firstName' => new Assert\NotBlank(),
            'lastName'  => new Assert\NotBlank(),
            'email'     => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'password'  => new Assert\NotBlank(),
        ]));

        /* If user is Authenticated */
        if (User::Auth($request->getHeaderLine('Authorization'))) {
            $response->getBody()->write('Запрос от авторизованного аккаунта');

            return $response->withStatus(403);
        }

        /* No errors, trying creating user */
        if ($violations->count() === 0) {
            if (User::where(['email' => $data->email])->first()) {
                $response->getBody()->write('Аккаунт с таким email уже существует');

                return $response->withStatus(409);
            }

            $user = new User([
                'firstName'    => $data->firstName,
                'lastName'     => $data->lastName,
                'email'        => $data->email,
                'passwordHash' => User::HashPassword($data->password),
            ]);

            if ($user->save()) {
                $this->log->info("New user created", [
                    'email' => $data->email,
                ]);
                $response->getBody()->write(json_encode([
                    'id'        => $user->id,
                    'firstName' => $user->firstName,
                    'lastName'  => $user->lastName,
                    'email'     => $user->email,
                ]));

                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(201);
            }

            return $response->withStatus(500);
        }

        /* If has errors */
        $errors = [];
        foreach ($violations as $violation) {
            /* @var ConstraintViolationInterface $violation */
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        $response->getBody()->write(json_encode($errors, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    public function searchId(Request $request, Response $response, array $args) {
        if (!User::Auth($request->getHeaderLine('Authorization'))) {
            $response->getBody()->write('Неверные авторизационные данные');

            return $response->withStatus(401);
        }
        $accoundId  = $args['accountId'];
        $violations = $this->validator->validate($accoundId, [
            new Assert\NotBlank,
            new Assert\Positive,
        ]);

        if ($violations->count() !== 0)
            return $response->withStatus(400);

        if (!$user = User::where(['id' => $accoundId])->first()) {
            $response->getBody()->write('Аккаунт с таким accountId не найден');

            return $response->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            "id"        => $user->id,
            "firstName" => $user->firstName,
            "lastName"  => $user->lastName,
            "email"     => $user->email,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    }

    public function searchParams(Request $request, Response $response) {
        if (!User::Auth($request->getHeaderLine('Authorization'))) {
            $response->getBody()->write('Неверные авторизационные данные');

            return $response->withStatus(401);
        }

        $params         = $request->getQueryParams();
        $params['from'] = $params['from'] ?: 0;
        $violations     = $this->validator->validate([
            'from' => $params['from'],
            'size' => $params['size'],
        ],
            new Assert\Collection([
                    'from' => [
                        new Assert\Type('int'),
                        new Assert\PositiveOrZero(),
                    ],
                    'size' => [
                        new Assert\NotBlank(),
                        new Assert\NotEqualTo(0),
                        new Assert\Positive(),
                    ],
                ]
            ));

        if ($violations->count() !== 0) {
            /* If has errors */
            $errors = [];
            foreach ($violations as $violation) {
                /* @var ConstraintViolationInterface $violation */
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            $response->getBody()->write(json_encode($errors, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        $params          = array_filter($params, fn($el) => $el != null && $el != "");
        $queryConditions = array_filter($params, fn($el) => in_array($el, [
                'firstName',
                'lastName',
                'email',
            ]
        ), ARRAY_FILTER_USE_KEY);

        /* @var Collection $users */
        $users = User::where($queryConditions)
            ->select('firstName', 'lastName', 'email')
            ->offset($params['from'])
            ->limit($params['size'])
            ->get();

        $response->getBody()->write(json_encode($users));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    }

    public function update(Request $request, Response $response, array $args) {
        if (!User::Auth($request->getHeaderLine('Authorization'))) {
            $error_message = 'Запрос от неавторизованного аккаунта или Неверные авторизационные данные';
            $response->getBody()->write($error_message);

            return $response->withStatus(401);
        }

        $json            = $request->getBody()->getContents();
        $data            = json_decode($json);
        $data->accountId = $args['accountId'];

        $violations = $this->validator->validate((array) $data, new Assert\Collection([
            'accountId' => [
                new Assert\NotBlank,
                new Assert\Positive,
            ],
            "firstName" => [
                new Assert\Type('string'),
                new Assert\NotBlank(),
            ],
            "lastName"  => new Assert\NotBlank(),
            "email"     => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            "password"  => new Assert\NotBlank(),
        ]));

        if ($violations->count() !== 0) {
            /* If has errors */
            $errors = [];
            foreach ($violations as $violation) {
                /* @var ConstraintViolationInterface $violation */
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            $response->getBody()->write(json_encode($errors, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        /**
         * Если пользователя с новым мылом нет
         * Обновляем, иначе 403
         *
         *
         */


    }
}