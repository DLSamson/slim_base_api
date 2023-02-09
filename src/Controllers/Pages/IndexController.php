<?php

namespace Api\Controllers\Pages;

use Api\Controllers\RenderController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends RenderController {
    public function __invoke(Request $request, Response $response, $args) {

        $content = $this->fenom->fetch('index.tpl', ['title' => 'SUCCESS!', 'test' => '<a href="https://ya.ru">Test</a>']);
        $response->getBody()->write($content);

        return $response
            ->withStatus(200);
    }
}