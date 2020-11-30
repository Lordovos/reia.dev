<?php
declare(strict_types=1);

namespace ReiaDev;

class User {
    public int $id;
    public string $username;
    public string $email;
    public int $role;

    public function __construct(int $id, string $username, string $email, int $role) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
    }
    public function isAdministrator(): bool {
        return ($this->role === \ReiaDev\Role::ADMINISTRATOR);
    }
}
