<?php

namespace Api\Core\Http;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController {
    public function __construct(LoggerInterface $log, ValidatorInterface $validator) {
        $this->log = $log;
        $this->validator = $validator;
    }
    protected LoggerInterface $log;
    protected ValidatorInterface $validator;
}