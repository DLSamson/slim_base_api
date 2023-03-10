<?php

namespace Api\Core\Validation\Validators;

use Api\Core\Validation\Constraints\EmptyString;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class EmptyStringValidator extends ConstraintValidator {

    public function validate($value, Constraint $constraint) {
        if (!$constraint instanceof EmptyString) {
            throw new UnexpectedTypeException($constraint, EmptyString::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match('/^[^\t\n\r\s]+$/', $value, $matches)) {
            // the argument must be a string or an object implementing __toString()
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}