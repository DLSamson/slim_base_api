<?php

namespace Api\Controllers;

use Api\Core\Http\BaseController;
use Api\Core\Models\Location;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class LocationController extends BaseController{
    public function search(Request $request, Response $response, $args) {
        $pointId = $args['pointId'];
        $result = $this->validate($pointId, new Assert\Positive(), $response);
        if($result !== true) return $result;

        $location = Location::where(['id' => $pointId])->first();
        if(!$location) return $response->withStatus(404);

        $response->getBody()->write(json_encode($location));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    }
}