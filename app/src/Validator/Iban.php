<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Iban extends Constraint
{
    public string $message = 'L\'IBAN "{{ value }}" n\'est pas valide.';
}
