<?php declare(strict_types=1);
require_once('vendor/autoload.php');

use PHPUnit\Framework\Testcase;
use ScriptTask\Database;

class DatabaseTest extends Testcase
{
    public function testCanGetLink()
    {
        $this->assertIsObject(Database::getLink());
    }
}