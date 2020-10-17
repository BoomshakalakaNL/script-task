<?php declare(strict_types=1);
require_once('vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use ScriptTask\Database;

class DatabaseTest extends TestCase
{
    public function testCanGetLink()
    {
        $this->assertIsObject(Database::getLink());
    }
}