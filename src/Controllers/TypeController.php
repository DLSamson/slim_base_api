<?php

namespace Api\Controllers;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Type;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;
use Api\Core\Validation\Constraints as OwnAssert;

class TypeController extends BaseController {
    public function search(Request $request, Response $response, $args) {
        $typeId = $args['typeId'];
        $errors = $this->validate($typeId, new Assert\Positive());
        if($errors) return ResponseFactory::BadRequest($errors);

        $type = Type::where(['id' => $typeId])->select('id', 'type')->first();
        if(!$type) return ResponseFactory::NotFound();

        return ResponseFactory::Success($type);
    }

    public function create(Request $request, Response $response, $args) {
        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data['type'],
            [new Assert\NotBlank(), new OwnAssert\NotEmptyString()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        if(Type::where(['type' => $data['type']])->first())
            return ResponseFactory::Conflict('Тип животного с таким type уже существует');

        $type = new Type(['type' => $data['type']]);
        if($type->save())
            return ResponseFactory::Created([
                'id' => $type->id,
                'type' => $type->type,
            ]);

        return ResponseFactory::InternalServerError();
    }

    public function update(Request $request, Response $response, $args) {
        $typeId = $args['typeId'];
        $errors = $this->validate($typeId, [new Assert\NotNull(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data, new Assert\Collection([
            'type' => [new Assert\NotBlank(), new OwnAssert\NotEmptyString()]
        ]));
        if($errors) return ResponseFactory::BadRequest($errors);

        $data[] = ['id', '<>', $typeId];
        if(Type::where($data)->first())
            return ResponseFactory::Conflict('Тип животного с таким type уже существует');

        $type = Type::where(['id' => $typeId])->first();
        if(!$type) return ResponseFactory::NotFound('Тип животного с таким typeId не найден');

        $type->type = $data['type'];
        if($type->save())
            return ResponseFactory::Success([
                'id' => $type->id,
                'type' => $type->type
            ]);

        return ResponseFactory::InternalServerError();
    }
    public function delete(Request $request, Response $response, $args) {
        $typeId = $args['typeId'];
        $errors = $this->validate($typeId, [new Assert\NotNull(), new Assert\Positive()]);
        if($errors) return ResponseFactory::BadRequest($errors);

        $type = Type::where(['id' => $typeId])->first();
        if(!$type)
            return ResponseFactory::NotFound('Тип животного с таким typeId не найден');

        if($type->animals()->first())
            return ResponseFactory::BadRequest('Есть животные с типом с typeId');

        if($type->delete())
            return ResponseFactory::Success();

        return ResponseFactory::InternalServerError();
    }
}