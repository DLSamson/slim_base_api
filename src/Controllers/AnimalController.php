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

    public function searchParams(Request $request, Response $response, array $args) {
        $params = $request->getQueryParams();
        $params['from'] = $params['from'] === null ? 0 : $params['from'];
        $params['size'] = $params['size'] === null ? 10 : $params['size'];

        $result = $this->validate($params, new Assert\Collection([
                'from' => [
                    new Assert\NotBlank(),
                    new Assert\PositiveOrZero(),
                ],
                'size' => [
                    new Assert\NotBlank(),
                    new Assert\Positive(),
                ],
                'startDateTime' => new Assert\DateTime(),
                'endDateTime' => new Assert\DateTime(),
                'chipperId' => new Assert\Positive(),
                'chippingLocationId' => new Assert\Positive(),
                'lifeStatus' => new Assert\EqualTo(['ALIVE', 'DEAD']),
                'gender' => new Assert\EqualTo(['MALE', 'FEMALE', 'OTHER']),
            ]
        ), $response);
        if($result !== true) return $result;

        /* @var Collection $animals */
        $animals = Animal::where($params)->get();
        if($animals->count() == 0) return $response->withStatus(404);

        return $response->withStatus(500);
    }
}