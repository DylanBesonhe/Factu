<?php

namespace App\Tests\Validator;

use App\Validator\Iban;
use App\Validator\IbanValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class IbanValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): IbanValidator
    {
        return new IbanValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new Iban());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new Iban());

        $this->assertNoViolation();
    }

    public function testValidFrenchIban(): void
    {
        $this->validator->validate('FR7630001007941234567890185', new Iban());
        $this->assertNoViolation();
    }

    public function testValidGermanIban(): void
    {
        $this->validator->validate('DE89370400440532013000', new Iban());
        $this->assertNoViolation();
    }

    public function testValidUkIban(): void
    {
        $this->validator->validate('GB82WEST12345698765432', new Iban());
        $this->assertNoViolation();
    }

    public function testValidSpanishIban(): void
    {
        $this->validator->validate('ES9121000418450200051332', new Iban());
        $this->assertNoViolation();
    }

    public function testInvalidIbanChecksum(): void
    {
        $constraint = new Iban();
        $this->validator->validate('FR7630001007941234567890186', $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', 'FR7630001007941234567890186')
            ->assertRaised();
    }

    public function testInvalidIbanFormat(): void
    {
        $constraint = new Iban();
        $this->validator->validate('INVALID', $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', 'INVALID')
            ->assertRaised();
    }

    public function testInvalidIbanTooShort(): void
    {
        $constraint = new Iban();
        $this->validator->validate('FR00', $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', 'FR00')
            ->assertRaised();
    }

    public function testInvalidIbanNoCountryCode(): void
    {
        $constraint = new Iban();
        $this->validator->validate('123456789012345678901234', $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', '123456789012345678901234')
            ->assertRaised();
    }

    public function testIbanWithSpacesIsValid(): void
    {
        $this->validator->validate('FR76 3000 1007 9412 3456 7890 185', new Iban());

        $this->assertNoViolation();
    }

    public function testIbanIsUppercased(): void
    {
        $this->validator->validate('fr7630001007941234567890185', new Iban());

        $this->assertNoViolation();
    }
}
