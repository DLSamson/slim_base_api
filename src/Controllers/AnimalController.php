<?php

namespace Api\Controllers;

use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class AnimalController extends BaseController {
    public function searchId(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $result = $this->validate($animalId,
            [new Assert\NotBlank(), new Assert\Positive()], $response);
        if($result !== true) return $result;

        $animal = Animal::where(['id' => $animalId])->first();
        if(!$animal) return $response->withStatus(404);

        return $response->withStatus(500);
    }

    public function searchParams(Request $request, Response $response) {
        $params = $request->getQueryParams();
        $params['from'] = $params['from'] === null ? 0 : $params['from'];
        $params['size'] = $params['size'] === null ? 10 : $params['size'];
        array_walk($params, 'trim');

        $result = $this->validate($params, new Assert\Collection([
                'from' => new Assert\Required([
                    new Assert\NotBlank(),
                    new Assert\PositiveOrZero(),
                ]),
                'size' => new Assert\Required([
                    new Assert\NotBlank(),
                    new Assert\Positive(),
                ]),
                'startDateTime' => new Assert\Optional(new Assert\DateTime()),
                'endDateTime' => new Assert\Optional(new Assert\DateTime()),
                'chipperId' => new Assert\Optional(new Assert\Positive()),
                'chippingLocationId' => new Assert\Optional(new Assert\Positive()),
                'lifeStatus' => new Assert\Optional(new Assert\EqualTo(['ALIVE', 'DEAD'])),
                'gender' => new Assert\Optional(new Assert\EqualTo(['MALE', 'FEMALE', 'OTHER'])),
            ]
        ), $response);
        if($result !== true) return $result;

        /* @var Collection $animals */
        $queryConditions = array_filter($params,
            fn($el) => !in_array($el, ['from', 'size']), ARRAY_FILTER_USE_KEY);
        $animals = Animal::where($queryConditions)
            ->limit($params['size'])
            ->offset($params['from'])
            ->get();
        $response->getBody()->write(json_encode($animals));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function locations(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $result = $this->validate($animalId, new Assert\Positive(), $response);
        if($result !== true) return $result;

        $params = $request->getQueryParams();
        $params['from'] = $params['from'] === null ? 0 : $params['from'];
        $params['size'] = $params['size'] === null ? 10 : $params['size'];

        $result = $this->validate($params, new Assert\Collection([
            'from' => new Assert\Required([new Assert\NotBlank(), new Assert\PositiveOrZero()]),
            'size' => new Assert\Required([new Assert\NotBlank(), new Assert\Positive()]),
            'startDateTime' => new Assert\Optional(new Assert\DateTime()),
            'endDateTime' => new Assert\Optional(new Assert\DateTime()),
        ]), $response);
        if($result !== true) return $result;

        $queryCondition = array_filter($params,
            fn($el) => !in_array($el, ['from', 'size']));

        $response->getBody()->write(json_encode([]));

        /* Выборка */
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}