<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Tva extends Constraint
{
    public string $message = 'Le numero de TVA "{{ value }}" n\'est pas valide.';
}
