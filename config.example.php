<?php
declare(strict_types=1);

/**
 * Copy this file to config.php and fill in your database credentials.
 * Do not commit config.php to version control if it contains real secrets.
 */
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'your_database_name',
        'user' => 'your_database_user',
        'pass' => 'your_database_password',
        'port' => 3306,
        // Optional socket path, only needed on some hosts.
        // 'socket' => '/path/to/mysql.sock',
        // Optional: set to true only in local/dev where DB auto-create is allowed.
        // 'auto_create' => false,
    ],
];
