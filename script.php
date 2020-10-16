<?php
// Turn of Notice errors
error_reporting(E_ALL & ~E_NOTICE);
require_once('vendor/autoload.php');

use ScriptTask\CsvToDatabaseInserter;
use ScriptTask\Database;

$opts = getopt('u:p:h:');

$filename = "users.csv";
$filepath = __DIR__."/".$filename;
$dbh = Database::getLink($opts['u'], $opts['p'], $opts['h']);

$program = new CsvToDatabaseInserter($filepath);
$program->insertUsers($dbh);




