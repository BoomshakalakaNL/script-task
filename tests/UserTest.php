<?php declare(strict_types=1);
require_once('vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use ScriptTask\User;

final class UserTest extends TestCase
{
    public function testNameIsSanitizedProperly()
    {
        $name = "fIlThY !! <> o'LastName?-van oranJe";
        $expected = "Filthy O'Lastname-van Oranje";
        $user = new User($name, $name, "");
        $this->assertEquals($expected, $user->sanitizeName($name));
        $this->assertEquals($expected, $user->name);
        $this->assertEquals($expected, $user->surname);
    }

    public function testEmailIsSanitizedProperly()
    {
        $email = "this</\/\/\/\/;:;;;:\/\////\\>shoulDBEREMoved@exaMPLE.com";
        $expected = "thisshouldberemoved@example.com";
        $user = new User("", "", $email);
        $this->assertEquals($expected, $user->sanitizeEmail($email));
        $this->assertEquals($expected, $user->email);

    }

    public function testValidationForName()
    {
        $goodName = "John Doe-O'Brien";
        $badName = "Jane Doe?";

        // $goodName = $user->name
        // $badName = $user->surname
        $user = new User($goodName, $badName, "");

        $this->assertTrue($user->validateName($goodName));
        $this->assertTrue($user->validateName($user->name));
        $this->assertNotTrue($user->validateName($badName));
        $this->assertTrue($user->validateName($user->surname));
    }

    public function testValidationForEmail()
    {
        $goodEmail = "test@example.com";
        $badEmail = "test@example@example.com";
        $unsanitizedEmail = "test</::;>@example.com";

        // constructor will sanitize email
        $user = new User("", "", $unsanitizedEmail);

        $this->assertTrue($user->validateEmail($goodEmail));
        $this->assertTrue($user->validateEmail($user->email));
        $this->assertNotTrue($user->validateEmail($badEmail));
        $this->assertNotTrue($user->validateEmail($unsanitizedEmail));
    }
}