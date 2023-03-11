<?php

namespace Api\Controllers;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Api\Core\Models\Location;
use Api\Core\Models\Type;
use Api\Core\Models\User;
use Api\Core\Services\DateFormatter;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class AnimalController extends BaseController {
    public function searchId(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId,
            [new Assert\NotBlank(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $animal = Animal::where(['id' => $animalId])->first();
        if(!$animal) return ResponseFactory::NotFound();

        $response->getBody()->write(json_encode($animal));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function searchParams(Request $request, Response $response) {
        $params = $request->getQueryParams();
        $params['from'] = $params['from'] === null ? 0 : $params['from'];
        $params['size'] = $params['size'] === null ? 10 : $params['size'];

        $errors = $this->validate($params, new Assert\Collection([
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
            'lifeStatus' => new Assert\Optional(new Assert\Choice([], ['ALIVE', 'DEAD'])),
            'gender' => new Assert\Optional(new Assert\Choice([], ['MALE', 'FEMALE', 'OTHER'])),
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

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
        $errors = $this->validate($animalId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $params = $request->getQueryParams();
        $params['from'] = $params['from'] === null ? 0 : $params['from'];
        $params['size'] = $params['size'] === null ? 10 : $params['size'];

        $errors = $this->validate($params, new Assert\Collection([
            'from' => new Assert\Required([new Assert\NotBlank(), new Assert\PositiveOrZero()]),
            'size' => new Assert\Required([new Assert\NotBlank(), new Assert\Positive()]),
            'startDateTime' => new Assert\Optional(new Assert\DateTime()),
            'endDateTime' => new Assert\Optional(new Assert\DateTime()),
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $queryCondition = array_filter($params,
            fn($el) => !in_array($el, ['from', 'size']));

        $response->getBody()->write(json_encode([]));

        /* Выборка */
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function create(Request $request, Response $response, array $args) {
        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data, new Assert\Collection([
            "animalTypes" => [
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Type('numeric'),
                    new Assert\Positive(),
                ]),
            ],
            "weight" => [new Assert\NotNull(), new Assert\Positive()],
            "length" => [new Assert\NotNull(), new Assert\Positive()],
            "height" => [new Assert\NotNull(), new Assert\Positive()],
            "gender" => [new Assert\NotNull(), new Assert\Choice(Animal::genderValues())],
            "chipperId" => [new Assert\NotNull(), new Assert\Positive()],
            "chippingLocationId" => [new Assert\NotNull(), new Assert\Positive()],
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $errors = $this->validate($data['animalTypes'], new Assert\Unique());
        if($errors) return ResponseFactory::Conflict('Массив animalTypes содержит дубликаты');

        $types = Type::whereIn('id',$data['animalTypes'])
            ->select('id', 'type')->get();
        if($types->count() !== count($data['animalTypes']))
            return ResponseFactory::NotFound('Тип животного не найден');

        $location = Location::where(['id' => $data['chippingLocationId']])->first();
        if(!$location)
            return ResponseFactory::NotFound('Точка локации с chippingLocationId не найдена');

        $user = User::where(['id' => $data['chipperId']])->first();
        if(!$user)
            return ResponseFactory::NotFound('Аккаунт с chipperId не найден');

        $animal = new Animal([
            'weight' => $data['weight'],
            'length' => $data['length'],
            'height' => $data['height'],
            'gender' => $data['gender'],
            'lifeStatus' => 'ALIVE',
            'chipperId' => $data['chipperId'],
        ]);
        $animal->chippingLocationId = $data['chippingLocationId'];

        if(!$animal->save())
            return ResponseFactory::InternalServerError();

        foreach($data['animalTypes'] as $id)
            $animal->types()->attach($id);

        $animal = $animal->first();

        return ResponseFactory::Created([
            'id' => $animal->id,
            'animalTypes' => $animal->types()
                ->get()->map(fn($el) => $el->id),
            'weight' => $animal->weight,
            'length' => $animal->length,
            'height' => $animal->height,
            'gender' => $animal->gender,
            'lifeStatus' => $animal->lifeStatus,
            'chippingDateTime' => DateFormatter::formatToISO8601($animal->chippingDateTime),
            'chipperId' => $animal->chipperId,
            'chippingLocationId' => $animal->chippingLocationId,
            'visitedLocations' => $animal->locations()
                ->get()->map(fn($el) => $el->id),
            'deathDateTime' => DateFormatter::formatToISO8601($animal->deathDateTime),
        ]);
    }

    public function update(Request $request, Response $response, array $args) {

    }

    public function delete(Request $request, Response $response, array $args) {

    }
}