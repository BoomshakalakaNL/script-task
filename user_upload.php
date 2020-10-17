<?php
// Turn of Notice errors
error_reporting(E_ALL & ~E_NOTICE);
require_once('vendor/autoload.php');

use ScriptTask\CsvToDatabaseInserter;
use ScriptTask\Database;
use ScriptTask\Logger;

$OPTIONS = array(
    [
        'short' => "",
        'long' => "file:",
        'description' => "  --file\t <file>\t\t this is the name of the CSV to be parsed\n"
    ],
    [
        'short' => "",
        'long' => "create_table",
        'description' => "  --create_table \t\t this will cause the MySQL users table to be built (and no further action will be taken)\n"
    ],
    [
        'short' => "",
        'long' => "dry_run",
        'description' => "  --dry_run \t\t\t this will be used with the --file directive and runs without altering database\n"
    ],
    [
        'short' => "u:",
        'long' => "",
        'description' => "  -u \t\t <string> \t MySQL username\n"
    ],
    [
        'short' => "p:",
        'long' => "",
        'description' => "  -p \t\t <string> \t MySQL password\n"
    ],
    [
        'short' => "h:",
        'long' => "",
        'description' => "  -h \t\t <string>\t MySQL host\n"
    ],
    [
        'short' => "",
        'long' => "help",
        'description' => "  --help  \t\t\t this outputs the details above\n"
    ]
);

// Variables for options short and long
$shortopts  = "";
$longopts = array();

// Add options to variables
foreach ($OPTIONS as $option) {
    $shortopts .= $option['short'];
    $longopts[] = $option['long'];
}

// Fetch options and store in associate array
$opts = getopt($shortopts, $longopts);

// For some reason, a option that takes no value gets value boolean(false)
if( $opts['help'] === false ) {
    echo "Usage: \n Options:\tValue:\t\tDescription:\n";
    foreach ($OPTIONS as $option) {
        echo $option['description'];
    }
    exit();
}

// All options below will the following variables
$dbh = Database::getLink($opts['u'], $opts['p'], $opts['h']);
$logger = new Logger();
$program = new CsvToDatabaseInserter($logger, $dbh);

// Option --create_table
if( $opts['create_table'] === false ) {
    // Because it's required to REbuild the table, I'll drop users table if exists
    $dbh->exec("DROP TABLE IF EXISTS users");
    if( !$program->createTable($dbh) ){
        $logger->error("Could not rebuild users table");
    }
    $logger->info("Succesfully rebuild users table");
    exit();
}

// If option --dry_run is present log to inform user, and begin dbTransaction
if ($opts['dry_run'] === false)
    $logger->info("Program will run in Dry Mode. No database alterations will be performed");


if( !$opts['file'] )
{
    $logger->alert("No filename given. Program can't operate. Usage example: $ php script.php --filename users.csv");
    exit();
}

$filename = $opts['file'];
$filepath = __DIR__."/".$filename;

if( !$program->setFile($filename) )
{
    $logger->error("Could not handle given file, change input");
    exit();
}

$program->parseUsers();

// If program was in Dry mode then roll back transactions
if( !isset($opts['dry_run']) ){
    $program->insertUsers();
}




