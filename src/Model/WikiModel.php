<?php
declare(strict_types=1);

namespace ReiaDev\Model;

class WikiModel extends Model {
    public function findSlug(string $slug): ?array {
        $sql = <<<SQL
            SELECT
                a.title,
                a.slug,
                a.body,
                a.categories,
                a.created_by,
                a.created_at,
                a.last_modified_by,
                a.last_modified_at,
                a.is_hidden,
                a.is_locked,
                u.username AS last_modified_by_username
            FROM
                articles a
                LEFT JOIN
                    users u
                    ON a.last_modified_by = u.id
            WHERE
                slug = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$slug]);
        $article = $stmt->fetch();

        if ($article) {
            return $article;
        }
        return null;
    }
    public function findAll(): array {
        $sql = <<<SQL
            SELECT
                title,
                slug,
                body,
                categories,
                created_by,
                created_at,
                last_modified_by,
                last_modified_at,
                is_hidden,
                is_locked
            FROM
                articles;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function addArticle(string $title, string $slug, string $body, string $categories, int $createdBy, string $createdAt): void {
        $sql = <<<SQL
            INSERT INTO
                articles (title, slug, body, categories, created_by, created_at, last_modified_by, last_modified_at)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?);
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$title, $slug, $body, $categories, $createdBy, $createdAt, $createdBy, $createdAt]);
    }
    public function hideArticle(int $isHidden, string $slug): void {
        $sql = <<<SQL
            UPDATE
                articles
            SET
                is_hidden = ?
            WHERE
                slug = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$isHidden, $slug]);
    }
    public function lockArticle(int $isLocked, string $slug): void {
        $sql = <<<SQL
            UPDATE
                articles
            SET
                is_locked = ?
            WHERE
                slug = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$isLocked, $slug]);
    }
    public function updateArticle(string $body, string $categories, int $lastModifiedBy, string $lastModifiedAt, string $slug): void {
        $sql = <<<SQL
            UPDATE
                articles
            SET
                body = ?,
                categories = ?,
                last_modified_by = ?,
                last_modified_at = ?
            WHERE
                slug = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$body, $categories, $lastModifiedBy, $lastModifiedAt, $slug]);
    }
}
