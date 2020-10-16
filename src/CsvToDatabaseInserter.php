<?php

namespace ScriptTask;

use Exception;
use PDO;
use PDOException;

class CsvToDatabaseInserter
{

    public array $users;

    public function __construct(string $filename)
    {
        $users = array();
        // Check if file exists
        if (!file_exists($filename)) exit('The given filename was not found.');


        // Open file then check if it can be opened
        $file = fopen($filename, "r");
        if (!$file) exit('Could not open file.');

        // Row counter for logging purposes
        $row = 0;

        while (($data = fgetcsv($file)) !== FALSE) {
            $row += 1;
            // Trim each element on row
            foreach ($data as &$item) $item = trim($item);

            // If row matches field names, then skip it
            if ($data == ['name', 'surname', 'email']) continue;

            // If the row is empty, then skip it
            if ($data == [""]) continue;

            // If row doesn't have 3 fields, tell user then skip it
            if (count($data) < 3) {
                echo "Could not parse data from line: $row\n";
                continue;
            }

            $users[] = new User($data[0], $data[1], $data[2]);
        }
        fclose($file);
        $this->users = $users;
    }

    public function createTable(PDO $dbh): bool
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `users` ( 
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(250) NOT NULL ,
                    `surname` VARCHAR(250) NOT NULL ,
                    `email` VARCHAR(250) NOT NULL ,
                    PRIMARY KEY (`id`),
                    UNIQUE `email` (`email`)
                ) ENGINE = InnoDB;";
            $dbh->exec($sql);
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }

        return true;
    }

    public function insertUsers(PDO $dbh)
    {
        if(!$this->createTable($dbh)){
            echo 'Failed to create users table and can\'t insert users';
            exit();
        }

        $sth = $dbh->prepare("INSERT INTO `users` (`name`, `surname`, `email`) VALUES (:name, :surname, :email)");


        foreach ($this->users as $user) {
            $sth->bindParam(':name', $user->name, PDO::PARAM_STR);
            $sth->bindParam(':surname', $user->surname, PDO::PARAM_STR);
            $sth->bindParam(':email', $user->email, PDO::PARAM_STR);
            $sth->execute();
        }
    }
}
