<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Controllers\AdminController;
use App\Models\User;

class AdminControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];  // vyčistit session před každým testem
        $_SERVER['REQUEST_URI'] = '/test-uri'; // aby fungoval is_active()
        require_once __DIR__ . '/../../App/Core/helpers.php';
    }

    public function testLoginSuccess()
    {
        // Mock user model
        $userMock = $this->createMock(User::class);
        $userMock->method('findByUsername')->willReturn([
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);

        $controller = new AdminController($userMock);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'admin';
        $_POST['password'] = 'password';

        ob_start();
        $result = $controller->login();
        ob_get_clean();

        // Ověříme, že login vrací URL pro přesměrování
        $this->assertEquals('/admin', $result);

        // Assert, že session obsahuje usera
        $this->assertArrayHasKey('user', $_SESSION);
        $this->assertEquals('admin', $_SESSION['user']['username']);
    }

    public function testLoginFail()
    {
        $userMock = $this->createMock(User::class);
        $userMock->method('findByUsername')->willReturn(null); // žádný uživatel

        $controller = new AdminController($userMock);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = 'wrong';
        $_POST['password'] = 'wrong';

        ob_start();
        $result = $controller->login();
        $output = ob_get_clean();

        // Login neprošel, vrací null
        $this->assertNull($result);

        // Výstup obsahuje chybovou hlášku
        $this->assertStringContainsString('Špatné uživatelské jméno nebo heslo.', $output);

        // Session neobsahuje uživatele
        $this->assertArrayNotHasKey('user', $_SESSION);
    }
}