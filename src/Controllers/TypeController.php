<?php

namespace Api\Controllers;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Type;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class TypeController extends BaseController {
    public function search(Request $request, Response $response, $args) {
        $typeId = $args['typeId'];
        $errors = $this->validate($typeId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $type = Type::where(['id' => $typeId])->first();
        if(!$type) return ResponseFactory::NotFound();

        $response->getBody()->write(json_encode($type));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}