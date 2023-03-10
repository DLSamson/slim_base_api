<?php

namespace Api\Core\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Api\Core\Validation\Validators\NotEmptyStringValidator;

class NotEmptyString extends Constraint {
    public $message = 'String is empty';

    public function validatedBy()
    {
        return NotEmptyStringValidator::class;
    }
}