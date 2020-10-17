<?php

namespace ScriptTask;

use Exception;
use PDO;
use PDOException;

/**
 * This class contains functions to parse data from a CSV file
 * and insert these into a DB.
 * 
 * @author Scott Verbeek
 */
class CsvToDatabaseInserter
{
    /**
     * Parsed users from CSV file
     * 
     * @var array
     */
    public array $users = [];

    /**
     * File from which to read users
     * 
     * @var resource
     */
    private $file;

    /**
     * Logger for outputting executions
     *
     * @var Logger 
     */
    private Logger $logger;

    /**
     * PDO object for database interaction
     * 
     * @var PDO
     */
    private PDO $dbh;

    /**
     * The construction of the class it takes one parameter which is a logger
     * and sets this to it's $logger property.
     * 
     * @param Logger $logger
     */
    public function __construct(Logger $logger, PDO $dbh)
    {
        $this->logger = $logger;
        $this->dbh = $dbh;
    }

    /**
     * Setter method for property $file, returns true if property was correctly set, otherwise false
     * 
     * Checks if: 
     * - file exists
     * - file is of type CSV
     * - file can open file
     * 
     * @param string $filename
     * @return bool
     */
    public function setFile(string $filename): bool
    {
        // Check if doesn't exist
        if (!file_exists($filename)) {
            $this->logger->notice("The given filename \"$filename\" has not been found");
            return false;
        }

        // Check if file extention isn't .csv
        if (!preg_match('/(.csv)$/', $filename)) {
            $this->logger->notice("The given filename \"$filename\" is not of type .csv");
            return false;
        }

        // Check if file can't be opened
        if (!$file = fopen($filename, "r")) {
            $this->logger->notice("The given filename \"$filename\" cannot be opened. File might be corrupted");
            return false;
        }

        $this->logger->info("Succesfully opened file \"$filename\"");
        $this->file = $file;
        return true;
    }

    /**
     * This method attempts to parse users from property $file
     * 
     * @return array
     */
    public function parseUsers(): array
    {
        // Row Counter for logging purpose
        $row = 0;
        $users = [];

        while (($data = fgetcsv($this->file)) !== FALSE) {
            $row += 1;
            // Trim each element on row
            foreach ($data as &$item) $item = trim($item);

            // If row matches field names, then skip it
            if ($data == ['name', 'surname', 'email']) continue;

            // If the row is empty, then skip it
            if ($data == [""]) continue;

            // If row doesn't have 3 fields, log and then skip it
            if (count($data) < 3) {
                $this->logger->notice("Could not parse data on line: $row. Data: $data");
                continue;
            }

            $user = new User($data[0], $data[1], $data[2]);
            if (
                $user->validateName($user->name) &&
                $user->validateName($user->surname) &&
                $user->validateEmail($user->email)
            ) {
                $users[] = $user;
                $this->logger->info("Succesfully parsed user from line $row");
            } else {
                $this->logger->notice("Parsed user data on line $row contains illegal values: $data[0],$data[1],$data[2]");
            }
        }
        $this->users = $users;
        return $users;
    }

    /**
     * Method for building users table. This table will store the data from CSV file.
     * The method will drop the table as per the rebuild requirement
     * 
     * @return bool
     */
    public function createTable(): bool
    {
        try {
            // Delete table if it exists
            $sql = "DROP TABLE IF EXISTS users;";
            $this->dbh->exec($sql);

            // Build table
            $sql = "CREATE TABLE `users` ( 
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(250) NOT NULL ,
                    `surname` VARCHAR(250) NOT NULL ,
                    `email` VARCHAR(250) NOT NULL ,
                    PRIMARY KEY (`id`),
                    UNIQUE `email` (`email`)
                ) ENGINE = InnoDB;";
            $this->dbh->exec($sql);
        } catch (PDOException $e) {
            $this->logger->error("PDO Exception: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->logger->error("Exception: " . $e->getMessage());
            return false;
        }

        $this->logger->info("Succesfully (re)built users table in database");
        return true;
    }

    /**
     * This method inserts users from property $users
     * 
     * @return void
     */
    public function insertUsers(): void
    {
        try {
            $sth = $this->dbh->prepare("INSERT INTO `users` (`name`, `surname`, `email`) VALUES (:name, :surname, :email)");

            foreach ($this->users as $user) {
                $sth->bindParam(':name', $user->name, PDO::PARAM_STR);
                $sth->bindParam(':surname', $user->surname, PDO::PARAM_STR);
                $sth->bindParam(':email', $user->email, PDO::PARAM_STR);

                // Execute and check if insert unsuccesfull
                if (!$sth->execute()) {
                    $error = $sth->errorInfo();
                    $this->logger->notice("Could not insert user $user->name,$user->surname,$user->email. DB error: " . $error[0] . " " . $error[2]);
                }
            }
        } catch (PDOException $e) {
            $this->logger->error("PDO Exception: " . $e->getMessage());
        } catch (Exception $e) {
            $this->logger->error("Exception: " . $e->getMessage());
        }
    }
}
