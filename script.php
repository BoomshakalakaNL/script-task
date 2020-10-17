<?php
// Turn of Notice errors
error_reporting(E_ALL & ~E_NOTICE);
require_once('vendor/autoload.php');

use ScriptTask\CsvToDatabaseInserter;
use ScriptTask\Database;
use ScriptTask\Logger;

$opts = getopt('u:p:h:');

$filename = "users.csv";
$filepath = __DIR__."/".$filename;

$dbh = Database::getLink($opts['u'], $opts['p'], $opts['h']);
$logger = new Logger();

$program = new CsvToDatabaseInserter($logger, $dbh);

if( !$program->setFile($filename) )
{
    $logger->error("Could not handle given file, change input");
}

if( !$program->createTable($dbh) ){
    $logger->error("Could not (re)build users table");
}

$program->parseUsers();
$program->insertUsers();




