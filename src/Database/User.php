<?php
namespace Database;

require_once dirname(__DIR__) . '/Database/Connect.php';
use Database\Database;

class User extends Database
{
    public function getUsers()
    {
        return $this->select("SELECT * FROM users;");
    }
    
    public function createUser(string $user)
    {
        echo "new User: " . $user;
        $newUser = json_decode($user, true);
        $query = "INSERT INTO users (username, email, password) VALUES ('" . $newUser["name"] . "','" . $newUser["email"] . "','" . password_hash($newUser["password"],PASSWORD_BCRYPT, ['cost' => 10]) . "')";
        echo "\n QUERY : " , $query, "\n";
        $this->insert($query);
    }
}
