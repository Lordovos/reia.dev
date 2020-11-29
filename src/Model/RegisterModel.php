<?php
declare(strict_types=1);

namespace ReiaDev\Model;

class RegisterModel extends Model {
    public function checkIfExists(string $username, string $email): bool {
        $sql = <<<SQL
            SELECT
                username,
                email
            FROM
                users
            WHERE
                username ILIKE ?
                OR email ILIKE ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $email]);
        $user = $stmt->fetch();

        if ($user) {
            return true;
        }
        return false;
    }
    public function addUser(string $username, string $password, string $email, string $joinDate, int $role): void {
        $sql = <<<SQL
            INSERT INTO
                users (username, password, email, join_date, role)
            VALUES
                (?, ?, ?, ?, ?);
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $password, $email, $joinDate, $role]);
    }
}
