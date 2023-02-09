<?php

namespace Api\Controllers;

use Psr\Log\LoggerInterface;
use Fenom;

class RenderController extends BaseController {
    public function __construct(LoggerInterface $log, Fenom $fenom) {
        parent::__construct($log);
        $this->fenom = $fenom;
    }

    protected Fenom $fenom;
}