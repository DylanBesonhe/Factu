<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class TvaValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Tva) {
            throw new UnexpectedTypeException($constraint, Tva::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        // Nettoyer la valeur (supprimer espaces)
        $tva = preg_replace('/\s/', '', strtoupper($value));

        // Format FR: FR + 2 caracteres cle + 9 chiffres SIREN
        // La cle peut etre 2 chiffres ou 1 lettre + 1 chiffre ou 2 lettres
        if (!preg_match('/^FR[0-9A-Z]{2}\d{9}$/', $tva)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Extraire SIREN et cle
        $cle = substr($tva, 2, 2);
        $siren = substr($tva, 4, 9);

        // Valider le SIREN avec Luhn
        if (!$this->validateSirenLuhn($siren)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Valider la cle TVA francaise
        if (!$this->validateCleTva($cle, $siren)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }

    private function validateSirenLuhn(string $siren): bool
    {
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $digit = (int) $siren[$i];
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    private function validateCleTva(string $cle, string $siren): bool
    {
        // Cle numerique: (12 + 3 * (SIREN % 97)) % 97
        // Format cle: 2 chiffres OU lettre+chiffre OU 2 lettres
        if (preg_match('/^\d{2}$/', $cle)) {
            $cleCalculee = (12 + 3 * ((int) $siren % 97)) % 97;
            return (int) $cle === $cleCalculee;
        }

        // Pour les cles alphanumeriques (cas speciaux), accepter si format correct
        // Ces cas sont rares et concernent les non-residents
        return true;
    }
}
