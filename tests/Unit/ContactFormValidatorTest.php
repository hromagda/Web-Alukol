<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Validation\ContactFormValidator;

class ContactFormValidatorTest extends TestCase
{
    public function testValidDataPassesValidation()
    {
        $data = [
            'name' => 'Jan Novák',
            'email' => 'jan@example.com',
            'message' => 'Dobrý den, mám zájem o služby.',
            'gdpr' => 1,
        ];

        $errors = ContactFormValidator::validate($data);
        $this->assertEmpty($errors, "Validní data by neměla vracet chyby.");
    }

    public function testEmptyNameFailsValidation()
    {
        $data = [
            'name' => '',
            'email' => 'jan@example.com',
            'message' => 'Dobrý den.',
            'gdpr' => 1,
        ];

        $errors = ContactFormValidator::validate($data);
        $this->assertContains('Jméno je povinné.', $errors);
    }

    public function testInvalidEmailFailsValidation()
    {
        $data = [
            'name' => 'Jan Novák',
            'email' => 'neplatny-email',
            'message' => 'Dobrý den.',
            'gdpr' => 1,
        ];

        $errors = ContactFormValidator::validate($data);
        $this->assertContains('Zadejte platný e-mail.', $errors);
    }

    public function testEmptyMessageFailsValidation()
    {
        $data = [
            'name' => 'Jan Novák',
            'email' => 'jan@example.com',
            'message' => '',
            'gdpr' => 1,
        ];

        $errors = ContactFormValidator::validate($data);
        $this->assertContains('Zpráva je povinná.', $errors);
    }

    public function testGdprNotCheckedFailsValidation()
    {
        $data = [
            'name' => 'Jan Novák',
            'email' => 'jan@example.com',
            'message' => 'Dobrý den.',
            'gdpr' => null,
        ];

        $errors = ContactFormValidator::validate($data);
        $this->assertContains('Musíte souhlasit s podmínkami GDPR.', $errors);
    }
}
