<?php

namespace Api\Core\Http;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController {
    public function __construct(LoggerInterface $log, ValidatorInterface $validator) {
        $this->log = $log;
        $this->validator = $validator;
    }
    protected LoggerInterface $log;
    protected ValidatorInterface $validator;

    public function validate($data, $constraints) {

        $violations = $this->validator->validate($data, $constraints);

        if($violations->count() === 0) return [];

        /* If has errors */
        $errors = [];
        foreach ($violations as $violation) {
            /* @var ConstraintViolationInterface $violation */
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        return $errors;
    }
}