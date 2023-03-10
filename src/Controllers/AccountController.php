<?php

namespace Api\Controllers;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Services\Authorization;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Models\User;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;
use Illuminate\Database\Eloquent\Collection;

class AccountController extends BaseController {
    public function register(Request $request, Response $response) {
        $json = $request->getBody()->getContents();
        $data = json_decode($json, true);

        $errors = $this->validate($data, new Assert\Collection([
            'firstName' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'lastName'  => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'email'     => [
                new Assert\NotBlank(),
                new Assert\Email(),
            ],
            'password'  => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        if (User::where(['email' => $data['email']])->first())
            return ResponseFactory::Conflict('Аккаунт с таким email уже существует');

        $user = new User([
            'firstName'    => $data['firstName'],
            'lastName'     => $data['lastName'],
            'email'        => $data['email'],
            'passwordHash' => User::HashPassword($data['password']),
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

    public function searchId(Request $request, Response $response, array $args) {
        $accoundId  = $args['accountId'];
        $errors = $this->validate($accoundId, [
            new Assert\NotBlank,
            new Assert\Positive,
        ]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $user = User
            ::where(['id' => $accoundId])
            ->select("id", "firstName", "lastName", "email")
            ->first();

        if (!$user) return ResponseFactory::NotFound('Аккаунт с таким accountId не найден');

        $response->getBody()->write(json_encode($user));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    }

    public function searchParams(Request $request, Response $response) {
        $params         = $request->getQueryParams();
        $params['from'] = $params['from'] ?: 0;
        $params['size'] = $params['size'] !== null ? $params['size'] : 10;

        $errors     = $this->validate($params, new Assert\Collection([
            'firstName' => new Assert\Optional([new Assert\NotBlank(), new OwnAssert\NotEmptyString()]),
            'lastName' => new Assert\Optional([new Assert\NotBlank(), new OwnAssert\NotEmptyString()]),
            'email' => new Assert\Optional([new Assert\NotBlank(), new Assert\Email]),
            'from' => new Assert\Required([new Assert\NotBlank(), new Assert\PositiveOrZero()]),
            'size' => new Assert\Required([new Assert\NotBlank(), new Assert\Positive()]),
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $queryConditions = array_filter($params, fn($el) =>
            !in_array($el, ['from', 'size']), ARRAY_FILTER_USE_KEY);

        /* @var Collection $users */
        $users = User::where($queryConditions)
            ->select('id', 'firstName', 'lastName', 'email')
            ->offset($params['from'])
            ->limit($params['size'])
            ->get();

        $response->getBody()->write(json_encode($users));

        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    }

    public function update(Request $request, Response $response, array $args) {
        $accountId = $args['accountId'];
        $errors = $this->validate($accountId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data, new Assert\Collection([
            'firstName' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'lastName' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
            'email' => new Assert\Email(),
            'password' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()],
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $authHash = $request->getHeaderLine('Authorization');
        $email = Authorization::getAuthenticatedEmail($authHash);

        if(!$email)
            return ResponseFactory::Forbidden();

        if(User::where(['email' => $data['email']])->first())
            return ResponseFactory::Conflict('Аккаунт с таким email уже существует');

        $data['passwordHash'] = User::HashPassword($data['password']);
        unset($data['password']);

        User::where(['email' => $email])
            ->update($data);

        $user = User::where(['email' => $data['email']])
            ->select('id', 'firstName', 'lastName', 'email')
            ->first();


        if($user->save())
            return ResponseFactory::Success($user);

        return ResponseFactory::InternalServerError();
    }

    public function delete(Request $request, Response $response, array $args) {
        $accountId = $args['accountId'];
        $errors = $this->validate($accountId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $authHash = $request->getHeaderLine('Authorization');
        $email = Authorization::getAuthenticatedEmail($authHash);

        if(!$email)
            return ResponseFactory::Forbidden();


    }
}