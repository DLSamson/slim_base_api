<?php

namespace Api\Core\Validation\Validators;

use Api\Core\Validation\Constraints\DateTimeInISO_8601;
use Api\Core\Validation\Constraints\NotEmptyString;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DateTimeInISO_8601Validator extends ConstraintValidator
{

    public function validate($value, Constraint $constraint)
    {
        $regExp = '/(\d\d\d\d)(-)?(\d\d)(-)?(\d\d)(T)?(\d\d)(:)?(\d\d)(:)?(\d\d)(\.\d+)?(Z|([+-])(\d\d)(:)?(\d\d))/';

        if (!$constraint instanceof DateTimeInISO_8601) {
            throw new UnexpectedTypeException($constraint, DateTimeInISO_8601::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match($regExp, $value, $matches)) {
            // the argument must be a string or an object implementing __toString()
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}