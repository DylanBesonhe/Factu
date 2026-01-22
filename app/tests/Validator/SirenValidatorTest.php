<?php

namespace App\Tests\Validator;

use App\Validator\Siren;
use App\Validator\SirenValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class SirenValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): SirenValidator
    {
        return new SirenValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new Siren());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new Siren());

        $this->assertNoViolation();
    }

    public function testValidSiren732829320(): void
    {
        $this->validator->validate('732829320', new Siren());
        $this->assertNoViolation();
    }

    public function testValidSiren443061841(): void
    {
        $this->validator->validate('443061841', new Siren());
        $this->assertNoViolation();
    }

    public function testValidSiren552032534(): void
    {
        $this->validator->validate('552032534', new Siren());
        $this->assertNoViolation();
    }

    public function testInvalidSiren123456789(): void
    {
        $constraint = new Siren();
        $this->validator->validate('123456789', $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '123456789')
            ->assertRaised();
    }

    public function testInvalidSirenTooShort(): void
    {
        $constraint = new Siren();
        $this->validator->validate('12345678', $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '12345678')
            ->assertRaised();
    }

    public function testInvalidSirenTooLong(): void
    {
        $constraint = new Siren();
        $this->validator->validate('1234567890', $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '1234567890')
            ->assertRaised();
    }

    public function testInvalidSirenWithLetter(): void
    {
        $constraint = new Siren();
        $this->validator->validate('12345678A', $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '12345678A')
            ->assertRaised();
    }

    public function testSirenWithSpacesIsValid(): void
    {
        $this->validator->validate('732 829 320', new Siren());

        $this->assertNoViolation();
    }
}
