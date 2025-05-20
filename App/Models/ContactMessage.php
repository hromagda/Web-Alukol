<?php

namespace App\Models;

class ContactMessage
{
    public static function save($name, $email, $message, $phone = null, $locality = null)
    {
        $pdo = get_pdo();

        $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, message, phone, locality)
        VALUES (?, ?, ?, ?, ?)
    ");

        return $stmt->execute([$name, $email, $message, $phone, $locality]);
    }
}