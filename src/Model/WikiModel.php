<?php
declare(strict_types=1);

namespace ReiaDev\Model;

class WikiModel extends Model {
    public function findSlug(string $slug): ?array {
        $sql = <<<SQL
            SELECT
                a.id,
                a.title,
                a.slug,
                a.categories,
                a.is_hidden,
                a.is_locked,
                a.latest_revision,
                r.id AS revision_id,
                r.body,
                r.reason,
                r.created_by,
                r.created_at,
                u.username AS created_by_username
            FROM
                articles a
                LEFT JOIN
                    revisions r
                    ON a.latest_revision = r.id
                LEFT JOIN
                    users u
                    ON r.created_by = u.id
            WHERE
                a.slug = ?;
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
                categories,
                is_hidden,
                is_locked
            FROM
                articles
            ORDER BY
                title ASC;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function findRevisionId(string $slug, int $revisionId): ?array {
        $sql = <<<SQL
            SELECT
                a.id,
                a.title,
                a.slug,
                a.categories,
                a.is_hidden,
                a.is_locked,
                a.latest_revision,
                r.id AS revision_id,
                r.body,
                r.reason,
                r.created_by,
                r.created_at,
                r.article_id AS revision_article_id,
                u.username AS created_by_username
            FROM
                articles a
                LEFT JOIN
                    revisions r
                    ON r.id = ?
                LEFT JOIN
                    users u
                    ON r.created_by = u.id
            WHERE
                a.slug = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$revisionId, $slug]);
        $article = $stmt->fetch();

        if ($article) {
            return $article;
        }
        return null;
    }
    public function getRevisions(int $id): array {
        $sql = <<<SQL
            SELECT
                r.id,
                r.reason,
                r.created_by,
                r.created_at,
                u.username AS created_by_username
            FROM
                revisions r
                LEFT JOIN
                    users u
                    ON r.created_by = u.id
            WHERE
                r.article_id = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
    public function addArticle(string $title, string $slug, string $categories): array {
        $sql = <<<SQL
            INSERT INTO
                articles (title, slug, categories)
            VALUES
                (?, ?, ?)
            RETURNING id;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$title, $slug, $categories]);
        return $stmt->fetch();
    }
    public function addRevision(string $body, string $reason, int $createdBy, string $createdAt, int $articleId): array {
        $sql = <<<SQL
            INSERT INTO
                revisions (body, reason, created_by, created_at, article_id)
            VALUES
                (?, ?, ?, ?, ?)
            RETURNING id;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$body, $reason, $createdBy, $createdAt, $articleId]);
        return $stmt->fetch();
    }
    public function setLatestRevision(int $revisionId, int $id) {
        $sql = <<<SQL
            UPDATE
                articles
            SET
                latest_revision = ?
            WHERE
                id = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$revisionId, $id]);
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
    public function updateArticle(string $categories, string $slug): ?array {
        $sql = <<<SQL
            UPDATE
                articles
            SET
                categories = ?
            WHERE
                slug = ?
            RETURNING id;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$categories, $slug]);
        $article = $stmt->fetch();

        if ($article) {
            return $article;
        }
        return null;
    }
}
