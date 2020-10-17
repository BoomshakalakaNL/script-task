<?php

namespace ScriptTask;

use Exception;
use PDO;
use PDOException;

class Database
{
    private string $engine;
    private string $host;
    private string $database;
    private string $user;
    private string $pass;

    // Constructor is private because only getLink() function should be user outside this class
    private function __construct()
    {
        try {
            if(! $config = parse_ini_file(__DIR__."\..\config.ini", true) )
            {
                exit("Could not read \"config.ini\". Program can't continue.");
            }
            $this->engine = $config['dsn']['engine'];
            $this->host = $config['dsn']['host'];
            $this->database = $config['dsn']['database'];
            $this->user = $config['db_user'];
            $this->pass = $config['db_password'];
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
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
