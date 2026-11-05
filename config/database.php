<?php
/**
 * Database connection (XAMPP defaults: user "root", empty password).
 * Change these values if your MySQL setup is different.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'absolutesinematik');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns one shared PDO connection.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed. Start MySQL in XAMPP and import database/absolutesinematik.sql in phpMyAdmin.');
        }
    }

    return $pdo;
}
