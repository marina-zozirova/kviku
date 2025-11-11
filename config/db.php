<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
            $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'db',
            $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '5432',
            $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'yii2_messages'
    ),

    'username' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'yii2user',
    'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'yii2pass',
    'charset' => 'utf8',
    'enableSchemaCache' => false,
];