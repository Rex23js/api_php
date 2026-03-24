<?php

class Database
{
    private static ?PDO $instance = null;

    private const HOST = 'localhost';
    private const DB   = 'api_php';
    private const USER = 'root';
    private const PASS = '';

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DB . ';charset=utf8mb4';
            self::$instance = new PDO($dsn, self::USER, self::PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$instance;
    }
}
