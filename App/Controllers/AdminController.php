<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\BlogArticle;
use App\Models\User;
use App\Models\PromoOffer;
use App\Validation\ImageValidator;

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

    public function editArticle(string $id): void
    {
        $model = new BlogArticle();
        $article = $model->getById((int) $id);

        View::render('admin/edit_article', [
            'article' => $article
        ], 'Úprava článku');
    }

    public function updateArticle(): void
    {
        if (!$this->checkLogin()) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Neplatný CSRF token. Zkuste to prosím znovu.";
                header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($_POST['id'] ?? ''));
                exit;
            }

            $id = $_POST['id'] ?? null;
            $title = trim($_POST['title'] ?? '');
            $slug = slugify($title);
            $content = $_POST['content'] ?? '';

            if ($id && $title && $content) {
                $article = new BlogArticle();
                $imagePath = $_POST['existing_image'] ?? null;

                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['image']['tmp_name'];
                    $originalName = $_FILES['image']['name'];

                    // === VALIDACE OBRÁZKU ===
                    if (!\App\Validation\ImageValidator::hasValidExtension($originalName)) {
                        $_SESSION['error'] = "Nepovolená přípona souboru. Povolené: JPG, PNG, GIF, WEBP.";
                        header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($id));
                        exit;
                    }

                    if (!\App\Validation\ImageValidator::isValidMimeType($tmpName)) {
                        $_SESSION['error'] = "Nepovolený typ obrázku. Povolené typy: JPG, PNG, GIF, WEBP.";
                        header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($id));
                        exit;
                    }

                    if (!\App\Validation\ImageValidator::isBelowMaxSize($tmpName, 5_000_000)) {
                        $_SESSION['error'] = "Obrázek je příliš velký (max. 5 MB).";
                        header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($id));
                        exit;
                    }

                    if (!\App\Validation\ImageValidator::isWithinDimensions($tmpName, 3000, 2000)) {
                        $_SESSION['error'] = "Obrázek má příliš velké rozměry. Max. 3000×2000 px.";
                        header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($id));
                        exit;
                    }

                    // === UKLÁDÁNÍ SOUBORU ===
                    $uploadDir = __DIR__ . '/../../public/uploads/blog/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = uniqid('blog_') . '_' . basename($originalName);
                    $destination = $uploadDir . $fileName;

                    if (move_uploaded_file($tmpName, $destination)) {
                        $imagePath = 'uploads/blog/' . $fileName;

                        if (!empty($_POST['existing_image'])) {
                            $oldImagePath = __DIR__ . '/../../public/' . $_POST['existing_image'];
                            if (is_file($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }
                    }
                }

                $updated = $article->update($id, $title, $slug, $content, $imagePath);

                $_SESSION[$updated ? 'success' : 'error'] = $updated
                    ? "Článek byl úspěšně aktualizován."
                    : "Nepodařilo se aktualizovat článek.";
            } else {
                $_SESSION['error'] = "Všechna pole musí být vyplněna.";
            }

            header('Location: ' . url('admin/list_articles'));
            exit;
        }
    }
    public function listArticles(): void
    {
        if (!$this->checkLogin()) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        $blogModel = new BlogArticle();
        $articles = $blogModel->getAll();

        View::render('admin/list_articles', [
            'articles' => $articles,
            'username' => $_SESSION['user']['username'],
        ], 'Správa článků');
    }

    public function deleteArticle(string $id): void
    {
        if (!$this->checkLogin()) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        $blogModel = new BlogArticle();

        // Promazání podle ID, můžeš přidat i kontrolu, jestli článek existuje
        $blogModel->delete((int)$id);

        header('Location: ' . url('admin/list_articles'));
        exit;
    }

    public function createArticle(): void
    {
        if (!$this->checkLogin()) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        View::render('admin/create_article', [
            'username' => $_SESSION['user']['username'] ?? 'admin'
        ], 'Nový článek');
    }

    public function saveArticle(): void
    {
        if (!$this->checkLogin()) {
            header('Location: ' . url('admin/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = "Neplatný CSRF token.";
                header('Location: ' . url('admin/create_article'));
                exit;
            }


            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $slug = slugify($title);
            $imagePath = null;

            if ($title && $content) {
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['image']['tmp_name'];
                    $originalName = $_FILES['image']['name'];

                    if (!ImageValidator::hasValidExtension($originalName) ||
                        !ImageValidator::isValidMimeType($tmpName) ||
                        !ImageValidator::isBelowMaxSize($tmpName, 5_000_000) ||
                        !ImageValidator::isWithinDimensions($tmpName, 3000, 2000)) {

                        $_SESSION['error'] = "Neplatný obrázek. Ujisti se, že má max. 5 MB a rozměry do 3000×2000 px.";
                        header('Location: ' . url('admin/create_article'));
                        exit;
                    }

                    $uploadDir = __DIR__ . '/../../public/uploads/blog/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = uniqid('blog_') . '_' . basename($originalName);
                    $destination = $uploadDir . $fileName;

                    if (move_uploaded_file($tmpName, $destination)) {
                        $imagePath = 'uploads/blog/' . $fileName;
                    }
                }

                $articleModel = new BlogArticle();
                $created = $articleModel->insert($title, $slug, $content, $imagePath);

                $_SESSION[$created ? 'success' : 'error'] = $created
                    ? "Článek byl úspěšně přidán."
                    : "Nepodařilo se vytvořit článek.";
            } else {
                $_SESSION['error'] = "Všechna pole musí být vyplněna.";
            }

            header('Location: ' . url('admin/list_articles'));
            exit;
        }
    }
}