<?php

namespace Api\Core\Http;

use Psr\Log\LoggerInterface;
use Fenom;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RenderController extends BaseController {
    public function __construct(LoggerInterface $log, ValidatorInterface $validator, Fenom $fenom) {
        parent::__construct($log, $validator);
        $this->fenom = $fenom;
    }

    protected Fenom $fenom;
}