<?php

namespace Api\Core\Validation\Constraints;

use Api\Core\Validation\Validators\DateTimeInISO_8601Validator;
use Api\Core\Validation\Validators\NotEmptyStringValidator;
use Symfony\Component\Validator\Constraint;

class DateTimeInISO_8601 extends Constraint
{
    public $message = 'TimeStamp is not in ISO-8601 format';

    public function validatedBy()
    {
        return DateTimeInISO_8601Validator::class;
    }
}