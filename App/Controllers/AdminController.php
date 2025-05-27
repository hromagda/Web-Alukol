<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\BlogArticle;
use App\Models\User;
use App\Models\PromoOffer;
use App\Validation\ImageValidator;

/**
 * Class AdminController
 * Spravuje administrátorskou sekci (přihlášení, správa galerie, zpráv, článků apod.)
 */
class AdminController
{
    private $userModel;

    /**
     * AdminController constructor.
     * Inicializuje model pro práci s uživateli.
     */
    public function __construct()
    {
        // Použijeme nový konstruktor, který v Useru už volá get_pdo()
        $this->userModel = new User();
    }

    /**
     * Zobrazí přihlašovací formulář a zpracuje přihlášení.
     *
     * @return void
     */
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

    /**
     * Zobrazí úvodní administrátorský dashboard.
     *
     * @return void
     */
    public function dashboard(): void
    {
        $this->redirectIfNotLoggedIn();

        View::render('admin/dashboard', ['username' => $_SESSION['user']['username']], 'Administrátorská sekce');
    }

    /**
     * Ověří, zda je uživatel přihlášen jako administrátor.
     * Využívá session nebo cookie.
     *
     * @return bool
     */
    private function checkLogin(): bool
    {

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

    /**
     * Přesměruje na přihlašovací stránku, pokud uživatel není přihlášen.
     *
     * @return void
     */
    private function redirectIfNotLoggedIn(): void
    {
        if (!$this->checkLogin()) {
            header('Location: ' . url('admin/login'));
            exit;
        }
    }

    /**
     * Odhlásí uživatele a odstraní session i cookie.
     *
     * @return void
     */
    public function logout(): void
    {
        session_destroy();
        setcookie('user_id', '', time() - 3600, '/', '', false, true);
        header('Location: ' . url());
        exit;
    }

    /**
     * Zobrazí a zpracuje formulář pro úpravu textu akční nabídky.
     *
     * @return void
     */
    public function editOffer(): void
    {
        $this->redirectIfNotLoggedIn();

        $promoModel = new PromoOffer();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
                $error = "Neplatný CSRF token.";
            } else {
                // 1) Načteme obsah z formuláře
                $contentRaw = trim($_POST['content'] ?? '');

            // Převod HTML entit na znaky
                $contentDecoded = html_entity_decode($contentRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Odstranění HTML tagů
                $contentClean = strip_tags($contentDecoded);

                // 3) Uložíme do DB
                if ($promoModel->updateOffer($contentClean)) {
                    $success = "Text akční nabídky byl úspěšně uložen.";
                } else {
                    $error = "Nastala chyba při ukládání.";
                }
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

    /**
     * Správa galerie – nahrávání a mazání obrázků, generování náhledů.
     *
     * @return void
     */
    public function manageGallery(): void
    {
        $this->redirectIfNotLoggedIn();

        $galleryPath = __DIR__ . '/../../public/images/gallery';
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
                $error = "Neplatný CSRF token.";
            } else {
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

                        $thumbWidth = 300;
                        $thumbHeight = 200;

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

                            $scale = min($thumbWidth / $width, $thumbHeight / $height);
                            $newWidth = (int)($width * $scale);
                            $newHeight = (int)($height * $scale);

                            $thumb = imagecreatetruecolor($newWidth, $newHeight);

                            if ($imgType === 'image/png' || $imgType === 'image/gif') {
                                imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
                                imagealphablending($thumb, false);
                                imagesavealpha($thumb, true);
                            }

                            imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

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

    /**
     * Zobrazí přijaté zprávy z kontaktního formuláře.
     *
     * @return void
     */
    public function showMessages(): void
    {
        $this->redirectIfNotLoggedIn(); // Zabezpečení přístupu

        $model = new \App\Models\ContactMessage();
        $messages = $model->getAll(); // metoda, kterou přidáme v modelu

        View::render('admin/messages', ['messages' => $messages], 'Přijaté zprávy');
    }

    /**
     * Zobrazí přijaté zprávy z kontaktního formuláře.
     *
     * @return void
     */
    public function editArticle(string $id): void
    {
        $this->redirectIfNotLoggedIn();

        $model = new BlogArticle();
        $article = $model->getById((int) $id);

        View::render('admin/edit_article', [
            'article' => $article
        ], 'Úprava článku');
    }

    /**
     * Zpracuje uložení upraveného článku odeslaného z formuláře.
     *
     * @return void
     */
    public function updateArticle(): void
    {
        $this->redirectIfNotLoggedIn();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Pokud to není POST, jen přesměruj
            header('Location: ' . url('admin/list_articles'));
            exit;
        }

        // Ověření CSRF tokenu
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Neplatný CSRF token. Zkuste to prosím znovu.";
            header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($_POST['id'] ?? ''));
            exit;
        }

        // Získání a sanitizace vstupů
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $title = trim($_POST['title'] ?? '');
        $contentRaw = $_POST['content'] ?? '';

        // Sanitizace pro HTML obsah - dovolíme některé HTML tagy
        $allowedTags = '<p><a><br><strong><em><ul><ol><li><h1><h2><h3><blockquote><img>';
        $content = strip_tags($contentRaw, $allowedTags);

        // Kontrola povinných polí
        if (!$id || $title === '' || $content === '') {
            $_SESSION['error'] = "Všechna pole musí být vyplněna a ID musí být platné číslo.";
            header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($_POST['id'] ?? ''));
            exit;
        }

        $slug = slugify($title);

        $article = new BlogArticle();
        $imagePath = $_POST['existing_image'] ?? null;

        // Zpracování uploadu obrázku
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $originalName = $_FILES['image']['name'];

            // Validace přípony
            if (!\App\Validation\ImageValidator::hasValidExtension($originalName)) {
                $_SESSION['error'] = "Nepovolená přípona souboru. Povolené: JPG, PNG, GIF, WEBP.";
                header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($id));
                exit;
            }

            // Validace MIME typu
            if (!\App\Validation\ImageValidator::isValidMimeType($tmpName)) {
                $_SESSION['error'] = "Nepovolený typ obrázku. Povolené typy: JPG, PNG, GIF, WEBP.";
                header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($id));
                exit;
            }

            // Validace velikosti
            if (!\App\Validation\ImageValidator::isBelowMaxSize($tmpName, 5_000_000)) {
                $_SESSION['error'] = "Obrázek je příliš velký (max. 5 MB).";
                header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($id));
                exit;
            }

            // Validace rozměrů
            if (!\App\Validation\ImageValidator::isWithinDimensions($tmpName, 3000, 2000)) {
                $_SESSION['error'] = "Obrázek má příliš velké rozměry. Max. 3000×2000 px.";
                header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($id));
                exit;
            }

            // Uložení souboru
            $uploadDir = __DIR__ . '/../../public/uploads/blog/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid('blog_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($originalName));
            $destination = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $destination)) {
                $imagePath = 'uploads/blog/' . $fileName;

                // Smazání starého obrázku, pokud existuje
                if (!empty($_POST['existing_image'])) {
                    $oldImagePath = __DIR__ . '/../../public/' . $_POST['existing_image'];
                    if (is_file($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            } else {
                $_SESSION['error'] = "Nepodařilo se uložit obrázek.";
                header('Location: ' . url('admin/edit_article') . '?id=' . urlencode($id));
                exit;
            }
        }

        // Aktualizace článku v DB
        $updated = $article->update($id, $title, $slug, $content, $imagePath);

        $_SESSION[$updated ? 'success' : 'error'] = $updated
            ? "Článek byl úspěšně aktualizován."
            : "Nepodařilo se aktualizovat článek.";

        header('Location: ' . url('admin/list_articles'));
        exit;
    }
    public function listArticles(): void
    {
        $this->redirectIfNotLoggedIn();

        $blogModel = new BlogArticle();
        $articles = $blogModel->getAll();

        View::render('admin/list_articles', [
            'articles' => $articles,
            'username' => $_SESSION['user']['username'],
        ], 'Správa článků');
    }

    public function deleteArticle(string $id): void
    {
        $this->redirectIfNotLoggedIn();

        $blogModel = new BlogArticle();

        // Promazání podle ID, můžeš přidat i kontrolu, jestli článek existuje
        $blogModel->delete((int)$id);

        header('Location: ' . url('admin/list_articles'));
        exit;
    }

    public function createArticle(): void
    {
        $this->redirectIfNotLoggedIn();

        View::render('admin/create_article', [
            'username' => $_SESSION['user']['username'] ?? 'admin'
        ], 'Nový článek');
    }

    public function saveArticle(): void
    {
        $this->redirectIfNotLoggedIn();

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