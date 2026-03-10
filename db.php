<?php
declare(strict_types=1);

/**
 * MySQLi database bootstrapper.
 * Configuration sources (in priority order):
 * 1) Optional local config.php returning ['db' => [...]]
 * 2) Environment vars: DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SOCKET, DB_AUTO_CREATE
 * 3) Local defaults (127.0.0.1/root/fest)
 */
function db(): mysqli
{
    static $conn = null;
    if ($conn instanceof mysqli) {
        return $conn;
    }

    $config = db_config();
    $conn = db_connect($config);

    static $initialized = false;
    if (!$initialized) {
        initializeDatabase($conn);
        $initialized = true;
    }

    return $conn;
}

function db_config(): array
{
    $configFile = __DIR__ . '/config.php';
    $fileConfig = [];
    if (is_file($configFile)) {
        $loaded = include $configFile;
        if (is_array($loaded)) {
            $fileConfig = isset($loaded['db']) && is_array($loaded['db']) ? $loaded['db'] : $loaded;
        }
    }

    $host = db_config_value($fileConfig, 'host', 'DB_HOST', '127.0.0.1');
    $name = db_config_value($fileConfig, 'name', 'DB_NAME', 'fest');
    $user = db_config_value($fileConfig, 'user', 'DB_USER', 'root');
    $pass = db_config_value($fileConfig, 'pass', 'DB_PASS', '', true);
    $portRaw = db_config_value($fileConfig, 'port', 'DB_PORT', '3306');
    $socket = db_config_value($fileConfig, 'socket', 'DB_SOCKET', null, true);
    $autoCreateRaw = db_config_value($fileConfig, 'auto_create', 'DB_AUTO_CREATE', null, true);

    $port = is_numeric((string) $portRaw) ? (int) $portRaw : 3306;
    $defaultAutoCreate = db_is_local_host((string) $host);
    $autoCreate = db_to_bool($autoCreateRaw, $defaultAutoCreate);

    return [
        'host' => (string) $host,
        'name' => (string) $name,
        'user' => (string) $user,
        'pass' => (string) $pass,
        'port' => $port,
        'socket' => $socket !== null && $socket !== '' ? (string) $socket : null,
        'auto_create' => $autoCreate,
    ];
}

function db_config_value(array $fileConfig, string $key, string $envKey, ?string $default, bool $allowEmpty = false): ?string
{
    if (array_key_exists($key, $fileConfig) && is_scalar($fileConfig[$key])) {
        $value = (string) $fileConfig[$key];
        if ($allowEmpty || $value !== '') {
            return $value;
        }
    }

    $envValue = db_env_value($envKey);
    if ($envValue !== null && ($allowEmpty || $envValue !== '')) {
        return $envValue;
    }

    return $default;
}

function db_env_value(string $key): ?string
{
    $value = getenv($key);
    if ($value !== false) {
        return (string) $value;
    }
    if (isset($_ENV[$key])) {
        return (string) $_ENV[$key];
    }
    if (isset($_SERVER[$key])) {
        return (string) $_SERVER[$key];
    }
    return null;
}

function db_to_bool($value, bool $default): bool
{
    if ($value === null || $value === '') {
        return $default;
    }
    $normalized = strtolower((string) $value);
    if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
        return true;
    }
    if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
        return false;
    }
    return $default;
}

function db_is_local_host(string $host): bool
{
    return in_array(strtolower($host), ['127.0.0.1', 'localhost', '::1'], true);
}

function db_connect(array $config): mysqli
{
    $host = (string) $config['host'];
    $name = (string) $config['name'];
    $user = (string) $config['user'];
    $pass = (string) $config['pass'];
    $port = (int) $config['port'];
    $socket = $config['socket'] !== null ? (string) $config['socket'] : null;
    $autoCreate = (bool) $config['auto_create'];

    try {
        $conn = new mysqli($host, $user, $pass, $name, $port, $socket);
    } catch (mysqli_sql_exception $e) {
        if ((int) $e->getCode() === 1049 && $autoCreate) {
            return db_create_database_and_connect($config);
        }
        db_fail_connection($config, $e->getMessage());
    }

    if ($conn->connect_errno) {
        if ((int) $conn->connect_errno === 1049 && $autoCreate) {
            return db_create_database_and_connect($config);
        }
        db_fail_connection($config, $conn->connect_error);
    }

    if (!$conn->set_charset('utf8mb4')) {
        http_response_code(500);
        exit('Failed to set database charset utf8mb4.');
    }

    return $conn;
}

function db_create_database_and_connect(array $config): mysqli
{
    $host = (string) $config['host'];
    $name = (string) $config['name'];
    $user = (string) $config['user'];
    $pass = (string) $config['pass'];
    $port = (int) $config['port'];
    $socket = $config['socket'] !== null ? (string) $config['socket'] : null;

    try {
        $conn = new mysqli($host, $user, $pass, '', $port, $socket);
    } catch (mysqli_sql_exception $e) {
        db_fail_connection($config, $e->getMessage());
    }

    if ($conn->connect_errno) {
        db_fail_connection($config, $conn->connect_error);
    }

    $safeName = str_replace('`', '``', $name);
    if (!$conn->query("CREATE DATABASE IF NOT EXISTS `{$safeName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        db_fail_connection($config, 'Could not create database "' . $name . '".');
    }

    if (!$conn->select_db($name)) {
        db_fail_connection($config, 'Could not select database "' . $name . '".');
    }

    if (!$conn->set_charset('utf8mb4')) {
        http_response_code(500);
        exit('Failed to set database charset utf8mb4.');
    }

    return $conn;
}

function db_fail_connection(array $config, string $reason): void
{
    http_response_code(500);

    $message = 'Database connection failed.';
    if (db_looks_like_default_config($config)) {
        $message .= ' Configure DB_HOST, DB_NAME, DB_USER and DB_PASS (or create config.php).';
    }
    $message .= ' Reason: ' . $reason;

    exit($message);
}

function db_looks_like_default_config(array $config): bool
{
    return db_is_local_host((string) ($config['host'] ?? ''))
        && (string) ($config['name'] ?? '') === 'fest'
        && (string) ($config['user'] ?? '') === 'root';
}

function initializeDatabase(mysqli $db): void
{
    $db->query(
        'CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            is_approved TINYINT(1) NOT NULL DEFAULT 0,
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
            apply_deadline DATE,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
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
        $stmt = db_prepare('INSERT INTO users (username, password_hash, is_admin, is_approved) VALUES (?, ?, ?, ?)');
        db_execute($stmt, ['admin', password_hash('admin123', PASSWORD_DEFAULT), 1, 1]);
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
    $rows = db_stmt_fetch_all_assoc($stmt);
    if (!$rows) {
        return null;
    }
    return $rows[0];
}

function db_fetch_all(mysqli_stmt $stmt): array
{
    return db_stmt_fetch_all_assoc($stmt);
}

function db_fetch_column(mysqli_stmt $stmt)
{
    $row = db_fetch_one($stmt);
    if (!$row) {
        return null;
    }
    return array_shift($row);
}

function db_last_id(): int
{
    return db()->insert_id;
}

/**
 * Fetch all rows from an executed statement as associative arrays.
 * Works on hosts both with and without mysqlnd.
 */
function db_stmt_fetch_all_assoc(mysqli_stmt $stmt): array
{
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        if (!$result) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    $metadata = $stmt->result_metadata();
    if ($metadata === false) {
        return [];
    }

    $fields = $metadata->fetch_fields();
    if (!$fields) {
        return [];
    }

    $row = [];
    $bindParams = [];
    foreach ($fields as $field) {
        $row[$field->name] = null;
        $bindParams[] = &$row[$field->name];
    }

    if (!call_user_func_array([$stmt, 'bind_result'], $bindParams)) {
        return [];
    }

    $rows = [];
    while ($stmt->fetch()) {
        $current = [];
        foreach ($row as $key => $value) {
            $current[$key] = $value;
        }
        $rows[] = $current;
    }

    $stmt->free_result();

    return $rows;
}
