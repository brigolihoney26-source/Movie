<?php
/**
 * Database queries for movies and series (both live in the "titles" table).
 */

/** Columns every title query returns, including a comma-separated genre list. */
const TITLE_COLUMNS = "t.*, (
    SELECT GROUP_CONCAT(g.name ORDER BY g.name SEPARATOR ', ')
    FROM title_genres tg
    JOIN genres g ON g.id = tg.genre_id
    WHERE tg.title_id = t.id
) AS genres";

function find_title(int $id): ?array
{
    $stmt = db()->prepare('SELECT ' . TITLE_COLUMNS . ' FROM titles t WHERE t.id = :id');
    $stmt->execute([':id' => $id]);

    return $stmt->fetch() ?: null;
}

/** The big title at the top of a page: the requested one, or the default featured one. */
function get_featured(string $type, int $requestedId = 0): ?array
{
    if ($requestedId > 0) {
        $stmt = db()->prepare('SELECT ' . TITLE_COLUMNS . ' FROM titles t WHERE t.id = :id AND t.type = :type');
        $stmt->execute([':id' => $requestedId, ':type' => $type]);
        if ($row = $stmt->fetch()) {
            return $row;
        }
    }

    $stmt = db()->prepare(
        'SELECT ' . TITLE_COLUMNS . ' FROM titles t
         WHERE t.type = :type
         ORDER BY t.is_featured DESC, t.popularity DESC
         LIMIT 1'
    );
    $stmt->execute([':type' => $type]);

    return $stmt->fetch() ?: null;
}

/** "New Releases" strip. */
function get_premieres(string $type, int $limit = 5): array
{
    $stmt = db()->prepare(
        'SELECT ' . TITLE_COLUMNS . ' FROM titles t
         WHERE t.type = :type AND t.is_premiere = 1
         ORDER BY t.added_at DESC, t.id
         LIMIT :limit'
    );
    $stmt->bindValue(':type', $type);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/** "Latest Added" carousel. */
function get_latest(string $type, int $limit = 12): array
{
    $stmt = db()->prepare(
        'SELECT ' . TITLE_COLUMNS . ' FROM titles t
         WHERE t.type = :type
         ORDER BY t.added_at DESC, t.id
         LIMIT :limit'
    );
    $stmt->bindValue(':type', $type);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Catalog search with filters.
 * $filters keys: q (text), genre (genre name), year, sort ("popular" | "score").
 * Returns ['items' => [...], 'total' => int, 'pages' => int].
 */
function search_catalog(string $type, array $filters, int $page, int $perPage): array
{
    $where  = ['t.type = :type'];
    $params = [':type' => $type];

    if (!empty($filters['q'])) {
        $where[]          = 't.title LIKE :q';
        $params[':q']     = '%' . addcslashes($filters['q'], '%_\\') . '%';
    }

    if (!empty($filters['genre'])) {
        $where[] = 'EXISTS (
            SELECT 1 FROM title_genres tg
            JOIN genres g ON g.id = tg.genre_id
            WHERE tg.title_id = t.id AND g.name = :genre
        )';
        $params[':genre'] = $filters['genre'];
    }

    if (!empty($filters['year'])) {
        $where[]         = 't.year = :year';
        $params[':year'] = (int) $filters['year'];
    }

    $whereSql = implode(' AND ', $where);
    $orderSql = ($filters['sort'] ?? 'popular') === 'score'
        ? 't.rating DESC, t.popularity DESC, t.id'
        : 't.popularity DESC, t.id';

    $count = db()->prepare("SELECT COUNT(*) FROM titles t WHERE $whereSql");
    $count->execute($params);
    $total = (int) $count->fetchColumn();

    $pages = max(1, (int) ceil($total / $perPage));
    $page  = min(max(1, $page), $pages);

    $stmt = db()->prepare(
        'SELECT ' . TITLE_COLUMNS . " FROM titles t WHERE $whereSql ORDER BY $orderSql LIMIT :limit OFFSET :offset"
    );
    foreach ($params as $name => $value) {
        $stmt->bindValue($name, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
    $stmt->execute();

    return ['items' => $stmt->fetchAll(), 'total' => $total, 'pages' => $pages, 'page' => $page];
}

/** Release years available for the Filter menu. */
function get_years(string $type): array
{
    $stmt = db()->prepare('SELECT DISTINCT year FROM titles WHERE type = :type ORDER BY year DESC');
    $stmt->execute([':type' => $type]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/** Titles of the same type that share genres with the given one. */
function get_related(array $title, int $limit = 10): array
{
    $stmt = db()->prepare(
        'SELECT ' . TITLE_COLUMNS . ', COUNT(*) AS shared
         FROM titles t
         JOIN title_genres a ON a.title_id = t.id
         JOIN title_genres b ON b.genre_id = a.genre_id AND b.title_id = :id
         WHERE t.id <> :other_id AND t.type = :type
         GROUP BY t.id
         ORDER BY shared DESC, t.rating DESC
         LIMIT :limit'
    );
    $stmt->bindValue(':id', (int) $title['id'], PDO::PARAM_INT);
    $stmt->bindValue(':other_id', (int) $title['id'], PDO::PARAM_INT);
    $stmt->bindValue(':type', $title['type']);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/** Count one more view when someone presses "Watch Now". */
function add_view(int $id): void
{
    $stmt = db()->prepare('UPDATE titles SET popularity = popularity + 1 WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

/** How many titles still have no real poster image saved. */
function count_missing_posters(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM titles WHERE poster_url IS NULL')->fetchColumn();
}
