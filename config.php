<?php
// Configuration. Override with environment variables in production.
// DB_DRIVER: "sqlite" (zero-setup demo) or "mysql".
return [
    'db_driver' => getenv('DB_DRIVER') ?: 'sqlite',
    'sqlite_path' => __DIR__ . '/data/bookings.sqlite',
    'mysql' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'name' => getenv('DB_NAME') ?: 'booking_demo',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
    ],
    'admin_user' => getenv('ADMIN_USER') ?: 'admin',
    // Demo password is "admin123". Generate your own: php -r 'echo password_hash("yourpass", PASSWORD_DEFAULT);'
    'admin_hash' => getenv('ADMIN_HASH') ?: '$2y$10$Ob7sRUnvdPidJgUYADUhoegNwk2dYg7Tqht./v6LUtdLfD7kpLWsm',
    'services' => ['Consultation', 'Website Review', 'Support Session'],
];
