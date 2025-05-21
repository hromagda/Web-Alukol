<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\User;
use App\Models\PromoOffer;

class AdminController
{
    private $userModel;

    public function __construct()
    {
        // Použijeme nový konstruktor, který v Useru už volá get_pdo()
        $this->userModel = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->findByUsername($username);


            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];
                // Nastavit cookie pro trvalé přihlášení (14 dní)
                setcookie('user_id', $user['id'], time() + 1209600, '/', '', false, true);

                header('Location: ' . url('admin'));
                exit;
            } else {
                $error = "Špatné uživatelské jméno nebo heslo.";
                View::render('admin/login', ['error' => $error], 'Přihlášení administrátora');
                return;
            }
        }

        View::render('admin/login', [], 'Přihlášení administrátora');
    }

    public function dashboard(): void
    {
        if (!$this->checkLogin()) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        View::render('admin/dashboard', ['username' => $_SESSION['user']['username']], 'Administrátorská sekce');
    }

    private function checkLogin(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
            return true;
        }

        if (isset($_COOKIE['user_id'])) {
            $user = $this->userModel->findById((int)$_COOKIE['user_id']);
            if ($user && $user['role'] === 'admin') {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];
                return true;
            }
        }

        return false;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        setcookie('user_id', '', time() - 3600, '/', '', false, true);
        header('Location: ' . url());
        exit;
    }

    public function editOffer(): void
    {
        if (!$this->checkLogin()) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        $promoModel = new PromoOffer();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content'] ?? '');

            if ($promoModel->updateOffer($content)) {
                $success = "Text akční nabídky byl úspěšně uložen.";
            } else {
                $error = "Nastala chyba při ukládání.";
            }
        }

        $offer = $promoModel->getOffer();

        View::render('admin/edit_offer', [
            'username' => $_SESSION['user']['username'],
            'offer' => $offer,
            'success' => $success ?? null,
            'error' => $error ?? null,
        ], 'Upravit akční nabídku');
    }

    public function manageGallery(): void
    {
        if (!$this->checkLogin()) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        $galleryPath = __DIR__ . '/../../public/images/gallery';
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Mazání obrázku + náhledu
            if (!empty($_POST['delete_image'])) {
                $file = basename($_POST['delete_image']);
                $filePath = $galleryPath . '/' . $file;

                // Zjistíme název náhledu
                $fileParts = pathinfo($file);
                $thumbnailName = $fileParts['filename'] . '_nahled.' . $fileParts['extension'];
                $thumbnailPath = $galleryPath . '/' . $thumbnailName;

                $successMessages = [];
                $errorMessages = [];

                // Smazat hlavní obrázek
                if (is_file($filePath)) {
                    if (unlink($filePath)) {
                        $successMessages[] = "Obrázek <strong>$file</strong> byl smazán.";
                    } else {
                        $errorMessages[] = "Nepodařilo se smazat <strong>$file</strong>.";
                    }
                } else {
                    $errorMessages[] = "Obrázek <strong>$file</strong> neexistuje.";
                }

                // Smazat náhled
                if (is_file($thumbnailPath)) {
                    if (unlink($thumbnailPath)) {
                        $successMessages[] = "Náhled <strong>$thumbnailName</strong> byl smazán.";
                    } else {
                        $errorMessages[] = "Nepodařilo se smazat náhled <strong>$thumbnailName</strong>.";
                    }
                }

                $success = implode('<br>', $successMessages);
                $error = implode('<br>', $errorMessages);
            }

            // Nahrávání nového obrázku
            if (!empty($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
                $upload = $_FILES['new_image'];
                $filename = basename($upload['name']);
                $target = $galleryPath . '/' . $filename;

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array(mime_content_type($upload['tmp_name']), $allowedTypes)) {
                    $error = "Nepovolený formát obrázku.";
                } elseif (move_uploaded_file($upload['tmp_name'], $target)) {
                    $success = "Obrázek byl úspěšně nahrán.";

                    // === Vytvoření náhledu ===
                    $thumbnailName = pathinfo($filename, PATHINFO_FILENAME) . '_nahled.' . pathinfo($filename, PATHINFO_EXTENSION);
                    $thumbnailPath = $galleryPath . '/' . $thumbnailName;

                    // Nastav maximální šířku a výšku náhledu (např. 300x200)
                    $thumbWidth = 300;
                    $thumbHeight = 200;

                    // Vytvoření náhledu
                    $imgType = mime_content_type($target);
                    switch ($imgType) {
                        case 'image/jpeg':
                            $img = imagecreatefromjpeg($target);
                            break;
                        case 'image/png':
                            $img = imagecreatefrompng($target);
                            break;
                        case 'image/gif':
                            $img = imagecreatefromgif($target);
                            break;
                        default:
                            $img = null;
                    }

                    if ($img) {
                        $width = imagesx($img);
                        $height = imagesy($img);

                        // Výpočet poměru stran
                        $scale = min($thumbWidth / $width, $thumbHeight / $height);

                        $newWidth = (int)($width * $scale);
                        $newHeight = (int)($height * $scale);

                        $thumb = imagecreatetruecolor($newWidth, $newHeight);

                        // Pro průhlednost u PNG a GIF
                        if ($imgType === 'image/png' || $imgType === 'image/gif') {
                            imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
                            imagealphablending($thumb, false);
                            imagesavealpha($thumb, true);
                        }

                        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                        // Uložení náhledu podle typu
                        switch ($imgType) {
                            case 'image/jpeg':
                                imagejpeg($thumb, $thumbnailPath, 85);
                                break;
                            case 'image/png':
                                imagepng($thumb, $thumbnailPath);
                                break;
                            case 'image/gif':
                                imagegif($thumb, $thumbnailPath);
                                break;
                        }

                        imagedestroy($img);
                        imagedestroy($thumb);
                    } else {
                        $error = "Nepodařilo se vytvořit náhled obrázku.";
                    }
                } else {
                    $error = "Nahrání obrázku selhalo.";
                }
            }
        }

        // Výpis obrázků
        $images = array_filter(scandir($galleryPath), function ($file) {
            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)
                && strpos($file, '_nahled') === false;
        });

        View::render('admin/gallery', [
            'images' => $images,
            'success' => $success,
            'error' => $error,
        ], 'Správa galerie');
    }

    public function showMessages(): void
    {
        $model = new \App\Models\ContactMessage();
        $messages = $model->getAll(); // metoda, kterou přidáme v modelu

        View::render('admin/messages', ['messages' => $messages], 'Přijaté zprávy');
    }
}