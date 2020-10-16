<?php

namespace ScriptTask;

class User
{
    public string $name;
    public string $surname;
    public string $email;

    public function __construct(string $name, string $surname, string $email)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
    }

    public function printMe()
    {
        printf("%s %s, email: %s\n", $this->name, $this->surname, $this->email);
    }
}