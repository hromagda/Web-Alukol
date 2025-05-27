<?php

namespace App\Controllers;

use App\Models\ContactMessage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Core\View;
use App\Validation\ContactFormValidator;
use App\Core\MailView;
use App\Core\Logger;

/**
 * Kontroler pro zpracování kontaktního formuláře.
 */
class ContactController
{
    /**
     * Zobrazí stránku s kontaktním formulářem.
     *
     * @return void
     */
    public function index()
    {
        View::render('contact/index', [], 'Kontakt');
    }

    /**
     * Zpracuje odeslání kontaktního formuláře:
     * - Ověří honeypot a CSRF
     * - Validuje data
     * - Uloží zprávu do databáze
     * - Odesílá e-mail pomocí PHPMaileru
     *
     * V případě chyby (validace, odeslání e-mailu) se zobrazuje příslušná chybová zpráva.
     *
     * @return void
     */
    public function send()
    {
        //Kontrola honeypot
        if (!empty($_POST['website'])) {
            // Honeypot byl vyplněn – pravděpodobně bot
            View::render('contact/index', [
                'errors' => ['Zprávu se nepodařilo odeslat.'],
                'old' => $_POST
            ]);
            return;
        }

        $data = [
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'phone'    => trim($_POST['phone'] ?? ''),
            'locality' => trim($_POST['locality'] ?? ''),
            'message'  => trim($_POST['message'] ?? ''),
            'gdpr'     => $_POST['gdpr'] ?? null,
        ];

        // Ověření CSRF tokenu
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            View::render('contact/index', [
                'errors' => ['Neplatný CSRF token. Zkuste to prosím znovu.'],
                'old' => $data,
            ]);
            return;
        }

        $errors = ContactFormValidator::validate($data);

        if ($errors) {
            View::render('contact/index', [
                'errors' => $errors,
                'old' => $data,
            ]);
            return;
        }

        // Uložení do DB
        $contactModel = new ContactMessage();
        $contactModel->save($data['name'], $data['email'], $data['message'], $data['phone'], $data['locality']);

        // Odeslání e-mailu
        $config = require __DIR__ . '/../../config/mail.php';
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        try {
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];

            $mail->setFrom($config['from'], $config['from_name']);
            $mail->addAddress($config['to']);

            $mail->isHTML(true);
            $mail->Subject = 'Nová poptávka z webu Alukol';
            $mail->Body = MailView::render('contact_message', [
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'],
                'locality' => $data['locality'],
                'message'  => $data['message'],
            ]);

            $mail->send();

            View::render('contact/index', [
                'success' => 'Zpráva byla úspěšně odeslána.',
                'old' => []
            ]);
        } catch (Exception $e) {
            Logger::error('Chyba při odesílání e-mailu', [
                'exception' => $e->getMessage(),
                'data' => $data,
            ]);

            View::render('contact/index', [
                'errors' => ['Zprávu se nepodařilo odeslat. Zkuste to prosím později.'],
                'old' => $data
            ]);
        }
    }
}