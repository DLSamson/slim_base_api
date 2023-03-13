<?php

namespace Api\Controllers;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Location;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class LocationController extends BaseController{
    public function search(Request $request, Response $response, array $args) {
        $pointId = $args['pointId'];
        $errors = $this->validate($pointId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $location = Location::where(['id' => $pointId])
            ->select('id', 'latitude', 'longitude')->first();
        if(!$location) return ResponseFactory::NotFound();

        return ResponseFactory::Success($location);
    }

    public function create(Request $request, Response $response, array $args) {
        $json = $request->getBody();
        $data = json_decode($json, true,  JSON_BIGINT_AS_STRING);
        $errors = $this->validate($data, new Assert\Collection([
            'latitude' => [new Assert\NotNull(), new Assert\Range(['min' => -90, 'max' => 90])],
            'longitude' => [new Assert\NotNull(), new Assert\Range(['min' => -180, 'max' => 180])]
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        if(Location::where($data)->first())
            return ResponseFactory::Conflict('Точка локации с такими latitude и longitude уже существует');

        $location = new Location($data);
        if($location->save())
            return ResponseFactory::Created([
                "id" => $location->id,
                "latitude" => $location->latitude,
                "longitude" => $location->longitude,
            ]);

        return ResponseFactory::InternalServerError();
    }

    public function update(Request $request, Response $response, array $args) {
        $pointId = $args['pointId'];
        $errors = $this->validate($pointId, [new Assert\NotNull(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $json = $request->getBody();
        $data = json_decode($json, true,  JSON_BIGINT_AS_STRING);
        $errors = $this->validate($data, new Assert\Collection([
            'latitude' => [new Assert\NotNull(), new Assert\Range(['min' => -90, 'max' => 90])],
            'longitude' => [new Assert\NotNull(), new Assert\Range(['min' => -180, 'max' => 180])]
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $data[] = ['id', '<>', $pointId];
        if(Location::where($data)->first())
            return ResponseFactory::Conflict('Точка локации с такими latitude и longitude уже существует');

        $location = Location::where(['id' => $pointId])->first();
        if(!$location)
            return ResponseFactory::NotFound('Точка локации с таким pointId не найдена');

        $location->latitude = $data['latitude'];
        $location->longitude = $data['longitude'];
        if($location->save())
            return ResponseFactory::Success([
                'id' => $location->id,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
            ]);

        return ResponseFactory::InternalServerError();
    }

    public function delete(Request $request, Response $response, array $args) {
        $pointId = $args['pointId'];
        $errors = $this->validate($pointId, [new Assert\NotNull(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $location = Location::find($pointId);
        if(!$location)
            return ResponseFactory::NotFound('Точка локации с таким pointId не найдена');

        if($location->animals()->first() || $location->chippedAnimals()->first())
            return ResponseFactory::BadRequest('Точка локации связана с животным');

        if($location->delete())
            return ResponseFactory::Success();

        return ResponseFactory::InternalServerError();
    }
}