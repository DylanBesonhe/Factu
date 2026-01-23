<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Siret extends Constraint
{
    public string $message = 'Le SIRET "{{ value }}" n\'est pas valide.';
}
