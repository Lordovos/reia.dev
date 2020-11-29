<?php
declare(strict_types=1);

namespace ReiaDev\Model;

class LoginModel extends Model {
    public function verify(string $username, string $password): ?array {
        $sql = <<<SQL
            SELECT
                id,
                username,
                password,
                role
            FROM
                users
            WHERE
                username ILIKE ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user["password"])) {
                return $user;
            }
            return null;
        }
        return null;
    }
}
