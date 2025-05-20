<?php

namespace App\Validation;

class ContactFormValidator
{
    public static function validate(array $data): array
    {
        $errors = [];

        if (trim($data['name'] ?? '') === '') {
            $errors[] = 'Jméno je povinné.';
        }

        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Zadejte platný e-mail.';
        }

        if (trim($data['message'] ?? '') === '') {
            $errors[] = 'Zpráva je povinná.';
        }

        if (empty($data['gdpr'])) {
            $errors[] = 'Musíte souhlasit s podmínkami GDPR.';
        }

        return $errors;
    }
}
