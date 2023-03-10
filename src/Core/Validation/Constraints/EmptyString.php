<?php

namespace Api\Core\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Api\Core\Validation\Validators\EmptyStringValidator;

class EmptyString extends Constraint {
    public $message = 'String is empty';

    public function validatedBy()
    {
        return EmptyStringValidator::class;
    }
}