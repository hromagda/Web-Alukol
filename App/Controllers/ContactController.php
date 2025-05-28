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
    public function __construct()
    {
        $this->metaData = [
            'title' => 'Kontakt',
            'pageTitle' => 'Kontaktujte nás – Alukol montáže a servis hliníkových profilů',
            'description' => 'Kontaktujte nás prostřednictvím kontaktního formuláře na stránkách Alukol. Rychlá reakce a profesionální servis.',
            'keywords' => 'kontakt, hliníkové profily, servis, Alukol',
            'author' => 'Alukol',
            'ogTitle' => 'Kontakt – Alukol',
            'ogDescription' => 'Kontaktujte nás prostřednictvím kontaktního formuláře na stránkách Alukol. Rychlá reakce a profesionální servis.',
            'ogImage' => url('obrazky/nahled-fb/kontakt-fb.png'),
            'ogUrl' => url('https://www.alukol.cz/kontakt'),
            'ogType' => 'website',
            'locale' => 'cs_CZ'
        ];
    }

    private function renderContactPage(array $data = [])
    {
        View::render('contact/index', $data, ...array_values($this->metaData));
    }

    public function index()
    {
        $this->renderContactPage();
    }

    public function send()
    {
        // Honeypot check
        if (!empty($_POST['website'])) {
            $this->renderContactPage([
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

        // CSRF check
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->renderContactPage([
                'errors' => ['Neplatný CSRF token. Zkuste to prosím znovu.'],
                'old' => $data,
            ]);
            return;
        }

        $errors = ContactFormValidator::validate($data);

        if ($errors) {
            $this->renderContactPage([
                'errors' => $errors,
                'old' => $data,
            ]);
            return;
        }

        $contactModel = new ContactMessage();
        $contactModel->save($data['name'], $data['email'], $data['message'], $data['phone'], $data['locality']);

        // Send email
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
            $mail->Body = MailView::render('contact_message', $data);

            $mail->send();

            $this->renderContactPage([
                'success' => 'Zpráva byla úspěšně odeslána.',
                'old' => []
            ]);
        } catch (Exception $e) {
            Logger::error('Chyba při odesílání e-mailu', [
                'exception' => $e->getMessage(),
                'data' => $data,
            ]);

            $this->renderContactPage([
                'errors' => ['Zprávu se nepodařilo odeslat. Zkuste to prosím později.'],
                'old' => $data
            ]);
        }
    }
}