<?php

declare(strict_types=1);
require './vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use ScriptTask\CsvToDatabaseInserter;
use ScriptTask\Database;
use ScriptTask\Logger;

class CsvToDatabaseInserterTest extends TestCase
{
    private Logger $logger;
    private PDO $dbh;
    private string $file;
    private CsvToDatabaseInserter $csv2db;


    protected function setUp(): void
    {
        $this->logger = new Logger($enabled = false);
        $this->dbh = Database::getLink();
        $this->file = __DIR__ . "\\test.csv";
        $this->csv2db = new CsvToDatabaseInserter($this->logger, $this->dbh);
        $this->dbh->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->dbh->rollBack();
    }

    public function testCanOpenCsvFile()
    {
        $this->assertTrue($this->csv2db->setFile($this->file));
    }

    public function testCanNotOpenFileThatDoesNotExists()
    {
        $file = __DIR__ . "\\fake.csv";
        $this->assertNotTrue($this->csv2db->setFile($file));
    }

    public function testCanNotOpenNotCsvFile()
    {
        $file = __DIR__ . "\\CsvToDatabaseInserterTest.php";
        $this->assertNotTrue($this->csv2db->setFile($file));
    }

    public function testCanParseUsers()
    {
        $this->csv2db->setFile($this->file);
        $users = $this->csv2db->parseUsers();
        $this->assertNotNull($users[0]);
        $this->assertIsObject($users[0]);
        $this->assertObjectHasAttribute('name', $users[0]);
        $this->assertObjectHasAttribute('surname', $users[0]);
        $this->assertObjectHasAttribute('email', $users[0]);
    }

    public function testParseUserMethodDoesNotReadHeader()
    {
        $this->csv2db->setFile($this->file);
        $users = $this->csv2db->parseUsers();

        $this->assertNotEquals([
            "name",
            "surname",
            "email"
        ], [
            $users[0]->name,
            $users[0]->surname,
            $users[0]->email,
        ]);
    }

    public function testCanInsertParsedUsersToDatabase()
    {
        $this->csv2db->setFile($this->file);
        $users = $this->csv2db->parseUsers();
        $this->csv2db->insertUsers();

        $sql = 'SELECT email FROM users WHERE email = :email';
        $sth = $this->dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(array(':email' => $users[0]->email));
        $result = $sth->fetchAll();
        $this->assertEquals(1, count($result));
    }

    public function testCanCreateUsersTable()
    {
        $this->assertTrue($this->csv2db->createTable());
    }
}
