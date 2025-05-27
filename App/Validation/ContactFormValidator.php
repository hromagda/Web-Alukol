<?php

namespace App\Validation;

/**
 * Třída pro validaci dat kontaktního formuláře.
 */
class ContactFormValidator
{
    public static function validate(array $data): array
    {
        /**
         * Validuje data z kontaktního formuláře.
         *
         * Kontroluje, zda jsou vyplněna povinná pole:
         * - jméno (není prázdné)
         * - email (validní formát)
         * - zpráva (není prázdná)
         * - souhlas s GDPR (zaškrtnut)
         *
         * @param array $data Data formuláře ['name' => ..., 'email' => ..., 'message' => ..., 'gdpr' => ...]
         * @return string[] Pole chybových zpráv. Pokud je prázdné, validace proběhla úspěšně.
         */
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
