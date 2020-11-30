<?php
declare(strict_types=1);

namespace ReiaDev\Model;

class UserModel extends Model {
    public function findId(int $id): ?array {
        $sql = <<<SQL
            SELECT
                id,
                username,
                email,
                join_date,
                role
            FROM
                users
            WHERE
                id = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            return $user;
        }
        return null;
    }
    public function findUsername(string $username): ?array {
        $sql = <<<SQL
            SELECT
                id,
                username,
                email,
                join_date,
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
            return $user;
        }
        return null;
    }
    public function findAll(): array {
        $sql = <<<SQL
            SELECT
                id,
                username,
                email,
                join_date,
                role
            FROM
                users
            ORDER BY
                id ASC;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function updateRole(int $role, int $id): void {
        $sql = <<<SQL
            UPDATE
                users
            SET
                role = ?
            WHERE
                id = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$role, $id]);
    }
}
