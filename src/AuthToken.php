<?php
declare(strict_types=1);

namespace ReiaDev;

class AuthToken {
    public function isSecure(): bool {
        return ((!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") || $_SERVER["SERVER_PORT"] === 443);
    }
    public function generateCookie(string $selector, string $authenticator): void {
        $domain = (($_SERVER["HTTP_HOST"] !== "localhost") ? $_SERVER["HTTP_HOST"] : false);
        $secure = $this->isSecure();
        $options = [
            "expires" => time() + 864000,
            "path" => "/",
            "domain" => $domain,
            "secure" => $secure,
            "httponly" => true
        ];
        setcookie("remember_me", $selector . ":" . base64_encode($authenticator), $options);
    }
    public function destroyCookie(): void {
        $domain = (($_SERVER["HTTP_HOST"] !== "localhost") ? $_SERVER["HTTP_HOST"] : false);
        $secure = $this->isSecure();
        $options = [
            "expires" => time() - 3600,
            "path" => "/",
            "domain" => $domain,
            "secure" => $secure,
            "httponly" => true
        ];
        setcookie("remember_me", "", $options);
    }
    public function add(string $selector, string $authenticator, int $id): void {
        $sql = <<<SQL
            INSERT INTO
                auth_tokens (selector, token, user_id)
            VALUES
                (?, ?, ?);
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$selector, hash("sha256", $authenticator), $id]);
    }
    public function remove(int $id): void {
        $sql = <<<SQL
            DELETE FROM
                auth_tokens
            WHERE
                user_id = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
    }
    public function get(string $selector): ?array {
        $sql = <<<SQL
            SELECT
                selector,
                token,
                user_id
            FROM
                auth_tokens
            WHERE
                selector = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$selector]);
        $token = $stmt->fetch();

        if ($token) {
            return $token;
        }
        return null;
    }
    public function update(string $selector, string $authenticator, int $id): void {
        $sql = <<<SQL
            UPDATE
                auth_tokens
            SET
                selector = ?,
                token = ?
            WHERE
                user_id = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$selector, hash("sha256", $authenticator), $id]);
    }
}
