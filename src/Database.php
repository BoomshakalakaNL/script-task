<?php

namespace ScriptTask;

use PDO;
use PDOException;

class Database
{
    private $engine;
    private $host;
    private $database;
    private $user;
    private $pass;

    // Constructor is private because only getLink() function should be user outside this class
    private function __construct()
    {
        // NOTE: Load from config.ini
        $this->engine = 'mysql';
        $this->host = 'localhost';
        $this->database = 'todo';
        $this->user = 'root';
        $this->pass = '';
    }

    public static function getLink($user = null, $pass = null, $host = null): PDO
    {
        // Create instance of self, for default values
        $db = new Database();

        // If parameter $host isset, then adjust accordingly
        $dsn = $db->engine . ':dbname=' . $db->database . ";host=" . (($host) ? $host : $db->host);
        try {
            // If parameters $user or $pass are set use in connection
            return new PDO($dsn, ($user) ? $user : $db->user, ($pass) ? $pass : $db->pass);
        } catch (PDOException $e) {
            exit('Database connection failed: ' . $e->getMessage());
        }
    }
}
