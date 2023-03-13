<?php

namespace Api\Controllers;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Api\Core\Models\Location;
use Api\Core\Models\Type;
use Api\Core\Models\User;
use Api\Core\Services\AnimalDataFormatter;
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

        return ResponseFactory::Success(AnimalDataFormatter::prepareForRespone($animal));
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
            'startDateTime' => new Assert\Optional(new Assert\Time()),
            'endDateTime' => new Assert\Optional(new Assert\Time()),
            'chipperId' => new Assert\Optional(new Assert\Positive()),
            'chippingLocationId' => new Assert\Optional(new Assert\Positive()),
            'lifeStatus' => new Assert\Optional(new Assert\Choice([], ['ALIVE', 'DEAD'])),
            'gender' => new Assert\Optional(new Assert\Choice([], ['MALE', 'FEMALE', 'OTHER'])),
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $queryConditions = array_filter($params,
            fn($el) => !in_array($el, ['from', 'size']), ARRAY_FILTER_USE_KEY);

        /* @var Collection $animals */
        $animals = Animal::where($queryConditions)
            ->limit($params['size'])
            ->offset($params['from'])
            ->get();

        return ResponseFactory::Success(AnimalDataFormatter::prepareCollectionForRespone($animals));
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
            "gender" => [new Assert\NotBlank(), new Assert\Choice(Animal::genderValues())],
            "chipperId" => [new Assert\NotNull(), new Assert\Positive()],
            "chippingLocationId" => [new Assert\NotNull(), new Assert\Positive()],
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $errors = $this->validate($data['animalTypes'], new Assert\Unique());
        if($errors) return ResponseFactory::Conflict('Массив animalTypes содержит дубликаты');

        $types = Type::whereIn('id', $data['animalTypes'])
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

        $animal->types()->sync($data['animalTypes'], false);

        $animal = $animal->find($animal->id);

        return ResponseFactory::Created(
            AnimalDataFormatter::prepareForRespone($animal));
    }

    public function update(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId,
            [new Assert\NotBlank(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data, new Assert\Collection([
            "weight" => [new Assert\NotNull(), new Assert\Positive()],
            "length" => [new Assert\NotNull(), new Assert\Positive()],
            "height" => [new Assert\NotNull(), new Assert\Positive()],
            "gender" => [new Assert\NotBlank(), new Assert\Choice(Animal::genderValues())],
            "lifeStatus" => [new Assert\NotBlank(), new Assert\Choice(Animal::lifeStatusValues())],
            "chipperId" => [new Assert\NotNull(), new Assert\Positive()],
            "chippingLocationId" => [new Assert\NotNull(), new Assert\Positive()],
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $animal = Animal::where(['id' => $animalId])->first();
        if(!$animal)
            return ResponseFactory::NotFound('Животное с animalId не найдено');

        $visitedLocation = AnimalLocation::where(['animal_id' => $animalId])
            ->orderBy('dateTimeOfVisitLocationPoint', 'desc')->first();
        if($visitedLocation != null && $visitedLocation->location_id == $data['chippingLocationId'])
            return ResponseFactory::BadRequest('Новая точка чипирования 
                совпадает с первой посещенной точкой локации');

        if(!Location::where(['id' => $data['chippingLocationId']])->first())
            return ResponseFactory::NotFound('Точка локации с chippingLocationId не найдена');

        if(!User::find($data['chipperId']))
            return ResponseFactory::NotFound('Аккаунт с chipperId не найден');

        $animal->weight = $data['weight'];
        $animal->length = $data['length'];
        $animal->height = $data['height'];
        $animal->gender = $data['gender'];
        $animal->lifeStatus = $data['lifeStatus'];
        $animal->chipperId = $data['chipperId'];
        $animal->chippingLocationId = $data['chippingLocationId'];
        if($data['lifeStatus'] == 'DEAD')
            $animal->deathDateTime = DateFormatter::formatToISO8601('now');
        else
            $animal->deathDateTime = null;

        if($animal->save())
            return ResponseFactory::Success(
                AnimalDataFormatter::prepareForRespone($animal));
    }

    public function delete(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId,
            [new Assert\NotBlank(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $animal = Animal::where(['id' => $animalId])->first();
        if(!$animal)
            return ResponseFactory::NotFound('Животное с animalId не найдено');

        if($animal->locations()->first())
            return ResponseFactory::BadRequest('Животное покинуло локацию чипирования, 
                при этом есть другие посещенные точки');

        if($animal->delete())
            return ResponseFactory::Success();

        return ResponseFactory::InternalServerError();
    }

    public function addType(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId,
            [new Assert\NotNull(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $typeId = $args['typeId'];
        $errors = $this->validate($typeId,
            [new Assert\NotNull(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $animal = Animal::find($animalId);
        if(!$animal)
            return ResponseFactory::NotFound('Животное с animalId не найдено');

        if(!Type::find($typeId))
            return ResponseFactory::NotFound('Тип животного с typeId не найден');

        if($animal->types()->find($typeId))
            return ResponseFactory::Conflict('Тип животного с typeId уже есть у животного с animalId');

        $animal->types()->attach($typeId);
        return ResponseFactory::Created(AnimalDataFormatter::prepareForRespone($animal));
    }

    public function updateType(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId,
            [new Assert\NotNull(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data, new Assert\Collection([
            'oldTypeId' => [new Assert\NotNull(), new Assert\Positive()],
            'newTypeId' => [new Assert\NotNull(), new Assert\Positive()],
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $animal = Animal::find($animalId);
        if(!$animal)
            return ResponseFactory::NotFound('Животное с animalId не найдено');

        if(!Type::find($data['oldTypeId']))
            return ResponseFactory::NotFound('Тип животного с oldTypeId не найден');

        if(!Type::find($data['newTypeId']))
            return ResponseFactory::NotFound('Тип животного с newTypeId не найден');

        /* @var Collection $animalTypes */
        $animalTypes = $animal->types()->get();

        if(!$animalTypes->find($data['oldTypeId']))
            return ResponseFactory::NotFound('Типа животного с oldTypeId нет у животного с animalId');
        if($animalTypes->find($data['newTypeId']))
            return ResponseFactory::Conflict('Тип животного с newTypeId уже есть у животного с animalId');
        if($animalTypes->find($data['newTypeId']) && $animalTypes->find($data['oldTypeId']))
            return ResponseFactory::Conflict('Животное с animalId уже имеет типы с oldTypeId и newTypeId');

        $animal->types()->detach($data['oldTypeId']);
        $animal->types()->attach($data['newTypeId']);

        return ResponseFactory::Success(AnimalDataFormatter::prepareForRespone($animal));
    }

    public function deleteType(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId,
            [new Assert\NotNull(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $typeId = $args['typeId'];
        $errors = $this->validate($typeId,
            [new Assert\NotNull(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $animal = Animal::find($animalId);
        if(!$animal)
            return ResponseFactory::NotFound('Животное с animalId не найдено');

        if(!Type::find($typeId))
            return ResponseFactory::NotFound('Тип животного с typeId не найден');

        /* @var Collection $animalTypes */
        $animalTypes = $animal->types()->get()->keyBy('id');
        if(!$animalTypes->find($typeId))
            return ResponseFactory::NotFound('У животного с animalId нет типа с typeId');

        if($animalTypes->count() === 1)
            return ResponseFactory::BadRequest('У животного только один тип и это тип с typeId');

        if($animal->types()->detach($typeId))
            return ResponseFactory::Success(AnimalDataFormatter::prepareForRespone($animal));

        return ResponseFactory::InternalServerError();
    }


    public function locationsSearch(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $params = $request->getQueryParams();
        $params['from'] = $params['from'] === null ? 0 : $params['from'];
        $params['size'] = $params['size'] === null ? 10 : $params['size'];

        $errors = $this->validate($params, new Assert\Collection([
            'from' => new Assert\Required([new Assert\NotBlank(), new Assert\PositiveOrZero()]),
            'size' => new Assert\Required([new Assert\NotBlank(), new Assert\Positive()]),
            'startDateTime' => new Assert\Optional(new Assert\Time()),
            'endDateTime' => new Assert\Optional(new Assert\Time()),
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $queryCondition = array_filter($params,
            fn($el) => !in_array($el, ['from', 'size']), ARRAY_FILTER_USE_KEY);
        if($params['startDateTime'])
            $queryCondition[] = ['dateTimeOfVisitLocationPoint', '>', $params['startDateTime']];
        if($params['startDateTime'])
            $queryCondition[] = ['dateTimeOfVisitLocationPoint', '<', $params['endDateTime']];

        $animal = Animal::find($animalId);
        if(!$animal) return ResponseFactory::NotFound('Животное с animalId не найдено');

        $locations = AnimalLocation::where($queryCondition)
            ->where(['animal_id' => $animal->id])
            ->orderBy('dateTimeOfVisitLocationPoint', 'ASC')
            ->offset($params['from'])
            ->limit($params['size'])
            ->get();

        return ResponseFactory::Success($locations->map(fn($el) => [
            'id' => $el->id,
            'dateTimeOfVisitLocationPoint' => DateFormatter::formatToISO8601($el->dateTimeOfVisitLocationPoint),
            'locationPointId' => $el->location_id,
        ]));
    }

    public function locationAdd(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $pointId = $args['pointId'];
        $errors = $this->validate($pointId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $animal = Animal::find($animalId);
        if(!$animal) return ResponseFactory::NotFound('Животное с animalId не найдено');

        if(!Location::find($pointId))
            return ResponseFactory::NotFound('Точка локации с pointId не найдена');

        if($animal->lifeStatus === 'DEAD')
            return ResponseFactory::BadRequest('У животного lifeStatus = "DEAD"');

        /* @var Collection $visitedLocations */
        $visitedLocations = AnimalLocation::where(['animal_id' => $animalId])
            ->get()->sortByDesc('dateTimeOfVisitLocationPoint');

        if($animal->chippingLocationId == $pointId && $visitedLocations->count() === 0)
            return ResponseFactory::BadRequest('
                Животное находится в точке чипирования 
                и никуда не перемещалось, попытка добавить 
                точку локации, равную точке чипирования');

        if($visitedLocations->first()->location_id == $pointId)
            return ResponseFactory::BadRequest('
                Попытка добавить точку локации, в которой уже находится животное');

        $visitedLocation = new AnimalLocation([
            'animal_id' => $animalId,
            'location_id' => $pointId,
        ]);
        $visitedLocation->save();
        $visitedLocation = $visitedLocation->find($visitedLocation->id);

        return ResponseFactory::Created([
            'id' => $visitedLocation->id,
            'dateTimeOfVisitLocationPoint' =>
                DateFormatter::formatToISO8601($visitedLocation->dateTimeOfVisitLocationPoint),
            'locationPointId' => $visitedLocation->location_id,
        ]);
}

    public function locationUpdate(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data, new Assert\Collection([
            'visitedLocationPointId' => [new Assert\NotNull(), new Assert\Positive()],
            'locationPointId' => [new Assert\NotNull(), new Assert\Positive()],
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $animal = Animal::find($animalId);
        if(!$animal)
            return ResponseFactory::NotFound('Животное с animalId не найдено');

        $location = Location::find($data['locationPointId']);
        if(!$location)
            return ResponseFactory::NotFound('Точка локации с locationPointId не найден');

        /* @var Collection $visitedLocations */
        $visitedLocations = AnimalLocation::where([
            'animal_id' => $animalId,
        ])->get()->sortBy('dateTimeOfVisitLocationPoint');
        if(!$visitedLocations->find($data['visitedLocationPointId']))
            return ResponseFactory::NotFound('У животного нет объекта
                с информацией о посещенной точке локации с visitedLocationPointId.');

        if($visitedLocations->first()->location_id == $data['locationPointId'] &&
            $data['locationPointId'] == $animal->chippingLocationId
        )
            return ResponseFactory::BadRequest('Обновление первой посещенной точки на точку чипирования');

        $visitedLocationToUpdate = $visitedLocations->find($data['visitedLocationPointId']);
        if($visitedLocationToUpdate->location_id == $location->id)
            return ResponseFactory::BadRequest('Обновление точки на такую же точку');

        /* Проверяем, что предыдущая и последующая точки посещения не равны между собой */
        $visitedLocationToUpdateIndex = $visitedLocations->search(fn($el) => $el->id == $visitedLocationToUpdate->id);
        if($visitedLocationToUpdateIndex != 0)
            if($visitedLocations->get($visitedLocationToUpdateIndex - 1)->location_id == $location->id)
                return ResponseFactory::BadRequest('Обновление точки локации на точку, совпадающую с предыдущей точками');
        if($visitedLocationToUpdateIndex != $visitedLocations->count() - 1)
            if($visitedLocations->get($visitedLocationToUpdateIndex + 1)->location_id == $location->id)
                return ResponseFactory::BadRequest('Обновление точки локации на точку, совпадающую с предыдущей точками');

        $visitedLocationToUpdate->location_id = $location->id;
        if($visitedLocationToUpdate->save())
            return ResponseFactory::Success([
                'id' => $visitedLocationToUpdate->id,
                'dateTimeOfVisitLocationPoint' =>
                    DateFormatter::formatToISO8601($visitedLocationToUpdate->dateTimeOfVisitLocationPoint),
                'locationPointId' => $visitedLocationToUpdate->location_id,
            ]);
    }

    public function locationDelete(Request $request, Response $response, array $args) {
        $animalId = $args['animalId'];
        $errors = $this->validate($animalId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $visitedPointId = $args['visitedPointId'];
        $errors = $this->validate($visitedPointId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $animal = Animal::find($animalId);
        if(!$animal)
            return ResponseFactory::NotFound('Животное с animalId не найдено');

        /* @var Collection $visitedLocations*/
        $visitedLocations = AnimalLocation::where(['animal_id' => $animalId])
            ->get()->sortBy('dateTimeOfVisitLocationPoint');
        $visitedLocationIndex = $visitedLocations->search(fn($el) => $el->id == $visitedPointId);

        if(!$visitedLocations->find($visitedPointId))
            return ResponseFactory::NotFound('Объект с информацией о посещенной 
                точке локации с visitedPointId не найден 
                или 
                У животного нет объекта с информацией о посещенной 
                точке локации с visitedPointId');

        $visitedLocationToDelete = $visitedLocations->get($visitedPointId);
        if($visitedLocationToDelete->delete()) {
            if($visitedLocationIndex != $visitedLocations->count() - 1);
                if($visitedLocationToDelete->location_id ==
                        $visitedLocations[$visitedLocationIndex + 1]->location_id)
                    $visitedLocations[$visitedLocationIndex + 1]->delete();
            return ResponseFactory::Success();
        }

        return ResponseFactory::InternalServerError();
    }
}