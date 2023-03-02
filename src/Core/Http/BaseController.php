<?php

namespace Api\Core\Http;

use Psr\Log\LoggerInterface;

class BaseController {
    public function __construct(LoggerInterface $log) {
        $this->log = $log;
    }
    protected LoggerInterface $log;
}