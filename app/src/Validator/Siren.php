<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Siren extends Constraint
{
    public string $message = 'Le SIREN "{{ value }}" n\'est pas valide.';
}
