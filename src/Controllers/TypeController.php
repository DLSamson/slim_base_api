<?php

namespace Api\Controllers;

use Api\Core\Http\BaseController;
use Api\Core\Models\Type;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class TypeController extends BaseController {
    public function search(Request $request, Response $response, $args) {
        $typeId = $args['typeId'];
        $result = $this->validate($typeId, new Assert\Positive(), $response);
        if($result !== true) return $result;

        $type = Type::where(['id' => $typeId])->first();
        if(!$type) return $response->withStatus(404);

        $response->getBody()->write(json_encode($type));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}