<?php
declare(strict_types=1);

namespace ReiaDev\Model;
/**
 * TODO: General search for basic terms. Currently only supports category and
 * user searches. Also need to research a way to possible simplify the search
 * logic.
 */
class HomeModel extends Model {
    public function search(string $term): array {
        $results = [];
        preg_match("/cat(egory)?:([a-z0-9_-]+)/", $term, $categoryMatch);
        preg_match("/user:(.*)/", $term, $userMatch);

        if ($categoryMatch) {
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
                    position(? IN a.categories) > 0;
SQL;
            $db = \ReiaDev\Database::getInstance()->getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(1, $categoryMatch[2], \PDO::PARAM_STR);
            $stmt->execute();
            $articles = $stmt->fetchAll();

            if ($articles) {
                $results["articles"] = $articles;
            }
        }
        if ($userMatch) {
            $sql = <<<SQL
                SELECT
                    id,
                    username,
                    email,
                    role
                FROM
                    users
                WHERE
                    username ILIKE ?;
SQL;
            $db = \ReiaDev\Database::getInstance()->getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(1, $userMatch[1], \PDO::PARAM_STR);
            $stmt->execute();
            $users = $stmt->fetchAll();

            if ($users) {
                $results["users"] = $users;
            }
        }
        return $results;
    }
}
