<?php
namespace ElementCounter\Service;

use PDO;
use Exception;
use PDOException;
use RuntimeException;

/**
 * Handles: connection 
 * And: general database interactions
 */
class Database 
{
    /** 
     * @var PDO|null
     */
    private static ?PDO $pdo = null;

    /**
     * Used: a private constructor 
     * To: prevent direct creation
     * Of: the Database object
     * 
     * @throws Exception - Database connection failure
     * To be handled further down the execution (here)
     */
    private function __construct() {
        try {
            $config = require __DIR__ . '/../../config/db.php';

            /**
             * Form MySQL DSN (Data Source Name)
             * MySQL Default port: 3306
             */
            $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['dbname'];

            /**
             * Set: PDO
             * As: the default databse instance
             */
            self::$pdo = new PDO(
                $dsn, $config['db']['user'], 
                $config['db']['password']
            );

            /**
             * Set: the PDO error mode 
             * To: throw exception
             * On: database connection failure
             * So: we can catch 
             * And: handle the failure
             */
            self::$pdo->setAttribute(
                PDO::ATTR_ERRMODE, 
                PDO::ERRMODE_EXCEPTION
            );
        } catch (PDOException $e) {
            /**
             * Let: it bubble further up
             * With: the error message
             */
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get: and return the default db instance (PDO)
     * Or: null on failure
     * 
     * @throws RuntimeException
     * To: be handled further up the execution
     * By: \ElementCounter\Controller\RequestController
     * 
     * @return PDO|null
     */
    public static function buildAndReturnDbInstance()
    {
        try {
            if (self::$pdo === null) {
                new self();
            }

            return self::$pdo;
        } catch (Exception $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }
}