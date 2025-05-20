<?php

namespace App\Controllers;

use App\Models\ContactMessage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Core\View;
use App\Validation\ContactFormValidator;
use App\Core\MailView;


class ContactController
{
    public function index()
    {
        View::render('contact/index', [], 'Kontakt');
    }

    public function send()
    {
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
        ContactMessage::save($data['name'], $data['email'], $data['message'], $data['phone'], $data['locality']);

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
            View::render('contact/index', [
                'errors' => ['Zprávu se nepodařilo odeslat. Zkuste to prosím později.'],
                'old' => $data
            ]);
        }
    }
}