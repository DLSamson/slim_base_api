<?php

namespace Api\Core\Http;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Psr\Http\Message\ResponseInterface as Response;

class BaseController {
    public function __construct(LoggerInterface $log, ValidatorInterface $validator) {
        $this->log = $log;
        $this->validator = $validator;
    }
    protected LoggerInterface $log;
    protected ValidatorInterface $validator;

    public function validate($data, $constraints, $response) {
        $violations = $this->validator->validate($data, $constraints);

        if($violations->count() === 0) return true;

        /* If has errors */
        $errors = [];
        foreach ($violations as $violation) {
            /* @var ConstraintViolationInterface $violation */
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        $response->getBody()->write(json_encode($errors));

        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }
}