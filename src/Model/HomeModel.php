<?php
declare(strict_types=1);

namespace ReiaDev\Model;

class HomeModel extends Model {
    public function search(string $term): array {
        $results = [];
        preg_match("/cat(egory)?:([a-z0-9_-]+)/", $term, $categoryMatch);

        if ($categoryMatch) {
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
                    position(? IN a.categories) > 0;
SQL;
            $db = \ReiaDev\Database::getInstance()->getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(1, $categoryMatch[2], \PDO::PARAM_STR);
            $stmt->execute();
            $articles = $stmt->fetchAll();

            if ($articles) {
                $results["articles_by_category"] = $articles;
            }
        } else {
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
                    title ILIKE ?
                    OR body ILIKE ?;
SQL;
            $db = \ReiaDev\Database::getInstance()->getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(1, "%" . $term . "%", \PDO::PARAM_STR);
            $stmt->bindValue(2, "%" . $term . "%", \PDO::PARAM_STR);
            $stmt->execute();
            $articles = $stmt->fetchAll();

            if ($articles) {
                $results["articles"] = $articles;
            }
        }
        return $results;
    }
    public function addUploadedImage(string $url, int $width, int $height, int $createdBy, string $createdAt): void {
        $sql = <<<SQL
            INSERT INTO
                uploaded_images (url, width, height, created_by, created_at)
            VALUES
                (?, ?, ?, ?, ?);
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$url, $width, $height, $createdBy, $createdAt]);
    }
    public function removeUploadedImage(int $id): void {
        $sql = <<<SQL
            DELETE FROM
                uploaded_images
            WHERE
                id = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
    }
    public function findUploadedImageId(int $id): ?array {
        $sql = <<<SQL
            SELECT
                id,
                url
            FROM
                uploaded_images
            WHERE
                id = ?;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $uploadedImage = $stmt->fetch();

        if ($uploadedImage) {
            return $uploadedImage;
        }
        return null;
    }
    public function findAllUploadedImages(): array {
        $sql = <<<SQL
            SELECT
                ui.id,
                ui.url,
                ui.width,
                ui.height,
                ui.created_by,
                ui.created_at,
                u.username AS created_by_username
            FROM
                uploaded_images ui
                LEFT JOIN
                    users u
                    ON ui.created_by = u.id;
SQL;
        $db = \ReiaDev\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
