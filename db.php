<?php
declare(strict_types=1);

/**
 * MySQLi database bootstrapper.
 * Defaults can be overridden with env vars: DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT.
 */
function db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $name = getenv('DB_NAME') ?: 'fest';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $port = (int) (getenv('DB_PORT') ?: 3306);

    $conn = @new mysqli($host, $user, $pass, '', $port);
    if ($conn->connect_errno) {
        http_response_code(500);
        exit('Database connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');

    // Ensure database exists
    $conn->query("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if (!$conn->select_db($name)) {
        http_response_code(500);
        exit('Failed to select database.');
    }

    static $initialized = false;
    if (!$initialized) {
        initializeDatabase($conn);
        $initialized = true;
    }

    return $conn;
}

function initializeDatabase(mysqli $db): void
{
    $db->query(
        'CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $db->query(
        'CREATE TABLE IF NOT EXISTS parties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            event_date DATE,
            event_time TIME,
            location VARCHAR(255),
            share_code VARCHAR(32) NOT NULL UNIQUE,
            theme_accent VARCHAR(20),
            header_image TEXT,
            max_guests INT NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_parties_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $db->query(
        'CREATE TABLE IF NOT EXISTS submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            party_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            attending TINYINT(1) DEFAULT 1,
            guests INT DEFAULT 1,
            food_pref TEXT,
            message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_submissions_party FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    seedDefaultUser($db);
}

function seedDefaultUser(mysqli $db): void
{
    $result = $db->query('SELECT COUNT(*) AS c FROM users');
    $row = $result ? $result->fetch_assoc() : ['c' => 0];
    $count = (int) ($row['c'] ?? 0);
    if ($count === 0) {
        $stmt = db_prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        db_execute($stmt, ['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
    }
}

function randomShareCode(): string
{
    return bin2hex(random_bytes(6));
}

function db_prepare(string $sql): mysqli_stmt
{
    $stmt = db()->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        exit('Database prepare failed.');
    }
    return $stmt;
}

function db_execute(mysqli_stmt $stmt, array $params = []): mysqli_stmt
{
    if ($params) {
        $types = '';
        $values = [];
        foreach ($params as $param) {
            $types .= is_int($param) ? 'i' : (is_float($param) ? 'd' : 's');
            $values[] = $param;
        }
        $refs = [];
        foreach ($values as $i => $value) {
            $refs[$i] = &$values[$i];
        }
        array_unshift($refs, $types);
        if (!call_user_func_array([$stmt, 'bind_param'], $refs)) {
            http_response_code(500);
            exit('Database bind failed.');
        }
    }
    if (!$stmt->execute()) {
        http_response_code(500);
        exit('Database execute failed.');
    }
    return $stmt;
}

function db_fetch_one(mysqli_stmt $stmt): ?array
{
    $result = $stmt->get_result();
    if (!$result) {
        return null;
    }
    $row = $result->fetch_assoc();
    return $row ?: null;
}

function db_fetch_all(mysqli_stmt $stmt): array
{
    $result = $stmt->get_result();
    if (!$result) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

function db_fetch_column(mysqli_stmt $stmt)
{
    $result = $stmt->get_result();
    if (!$result) {
        return null;
    }
    $row = $result->fetch_row();
    return $row ? $row[0] : null;
}

function db_last_id(): int
{
    return db()->insert_id;
}
