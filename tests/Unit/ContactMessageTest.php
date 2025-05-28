<?php
namespace Tests\Unit;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use App\Models\ContactMessage;

class ContactMessageTest extends TestCase
{
    public function testSaveCallsExecuteAndReturnsTrue()
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with(['Jan Novák', 'jan@example.com', 'Zpráva', '123', 'Praha'])
            ->willReturn(true);

        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO contact_messages'))
            ->willReturn($stmtMock);

        $model = new ContactMessage($pdoMock);

        $result = $model->save('Jan Novák', 'jan@example.com', 'Zpráva', '123', 'Praha');
        $this->assertTrue($result);
    }

    public function testGetAllReturnsData()
    {
        $expectedData = [
            ['name' => 'Jan', 'email' => 'jan@example.com', 'message' => 'Ahoj'],
            ['name' => 'Petr', 'email' => 'petr@example.com', 'message' => 'Zdravím'],
        ];

        // Mock PDOStatement
        $stmtMock = $this->createMock(\PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        // Mock PDO
        $pdoMock = $this->createMock(\PDO::class);
        $pdoMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT * FROM contact_messages'))
            ->willReturn($stmtMock);

        $model = new \App\Models\ContactMessage($pdoMock);

        $result = $model->getAll();

        $this->assertSame($expectedData, $result);
    }
}
