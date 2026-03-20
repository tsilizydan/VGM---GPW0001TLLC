<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

/**
 * Base Model.
 * Provides a shared, lazily-initialised PDO connection.
 * Extend this class and add query methods per entity.
 */
abstract class Model
{
    private static ?PDO $connection = null;

    // -----------------------------------------------------------------------
    // Connection
    // -----------------------------------------------------------------------

    /**
     * Return the shared PDO connection.
     * Creates it on first call using settings from /config/database.php.
     *
     * @throws \RuntimeException  When the connection fails.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $config = require base_path('config/database.php');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$connection = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            // Do NOT expose credentials in production
            $message = env('APP_DEBUG', false)
                ? 'Database connection failed: ' . $e->getMessage()
                : 'Database connection failed. Please try again later.';

            throw new \RuntimeException($message, (int) $e->getCode(), $e);
        }

        return self::$connection;
    }

    // -----------------------------------------------------------------------
    // Convenience queries (extend in child models)
    // -----------------------------------------------------------------------

    /**
     * Public wrapper for external callers (e.g. Core\Lang) that need
     * DB access without extending Model.
     *
     * @param  list<mixed> $bindings
     * @return list<array<string, mixed>>
     */
    public static function rawQuery(string $sql, array $bindings = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /**
     * Run a SELECT query and return all rows.
     *
     * @param  list<mixed> $bindings
     * @return list<array<string, mixed>>
     */
    protected static function query(string $sql, array $bindings = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /**
     * Run a SELECT query and return the first row, or null.
     *
     * @param  list<mixed>              $bindings
     * @return array<string, mixed>|null
     */
    protected static function queryOne(string $sql, array $bindings = []): ?array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Execute an INSERT / UPDATE / DELETE and return affected row count.
     *
     * @param list<mixed> $bindings
     */
    protected static function execute(string $sql, array $bindings = []): int
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    /**
     * Return the last inserted ID.
     */
    protected static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }
}
