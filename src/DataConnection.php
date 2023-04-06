<?php

namespace dir2db;

use PDO;
use PDOStatement;
use PDOException;

/**
 * Class DataConnection extends Environment. PDO database class.
 */
class DataConnection extends Environment
{
    /** Object of PDO class @var Pdo */
    protected Pdo $dB;

    /** Object of Logger class @var Logger */
    private Logger $logger;

    public function __construct()
    {
        $this->parseIni();
        $this->logger = new Logger('dir2db');
        $this->dbConnect();
    }
    
    /**
     * Uses the database credentials to establish and set a 
     * property containing a PDO connection.
     * 
     * @throws Dir2dbException
     * 
     * @return bool
     */
    private function dbConnect(): bool
    {
        try {
            $this->dB = new PDO($GLOBALS['dsn'], $GLOBALS['username'], $GLOBALS['password']);
            $this->dB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        $this->dB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) { 
            $this->logExceptionBeforeThrowingDir2dbException($e);
        }
        return true;
    }

    /**
     * Safe way to fetch results from a single row using a PDO connection.
     * If return type is true this functions returns an array, otherwise
     * it returns an object of stdClass.
     *
     * @param string $query
     * @param ?array $array
     * @param bool $returnType
     * 
     * @throws Dir2dbException
     * 
     * @return object|array
     */
    public function preparedQueryRow(string $query, array $array=null, bool $returnType=false): object|array
    {
        try {
            $stmt = $this->prepareStatement($query, $array); 
            $stmt->execute();

            if (!$returnType) {
                $result = $stmt->fetch(PDO::FETCH_OBJ);
            } else {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $this->logExceptionBeforeThrowingDir2dbException($e);
        }

        return $result;
    }

    /**
     * Safe way to fetch results from multiple rows using a PDO connection.
     * If return type is true this functions returns an array, otherwise
     * it returns an object of stdClass.
     *
     * @param string $query
     * @param ?array $array
     * @param bool $returnType
     * 
     * @throws Dir2dbException
     * 
     * @return object|array
     */
    public function preparedQueryMany(string $query, array $array=null, bool $returnType=false): object|array
    {
        try {      
            $stmt = $this->prepareStatement($query, $array); 
            $stmt->execute();

            if (!$returnType) {
                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            } else {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $this->logExceptionBeforeThrowingDir2dbException($e);
        }
        
        return $result;
    }

    /**
     * Prepare an INSERT statement with bound parameters, returns bool or error message string.
     * 
     * @example ("INSERT INTO table (column, column, column) VALUES (?, ?, ?)",
     * array('column' => $var, 'column' => $var, 'column' => $var));
     * 
     * @param string $query
     * @param array $params
     * 
     * @throws Dir2dbException
     * 
     * @return bool|int
     */
    public function preparedInsert(string $query, array $params=null, bool $countRequired=false): bool|int
    {
        try {
            $stmt = $this->dB->prepare($query);
            if ($countRequired) {
                $stmt->execute($params);
                return $stmt->rowCount();
            } else {
                return $stmt->execute($params);
            }
        } catch (PDOException $e) {
            $this->logExceptionBeforeThrowingDir2dbException($e);
        }
    }

    /**
     * Piggy backs on the prepared insert function return the affected row count.
     *
     * @param string $query
     * @param array $params
     * 
     * @throws Dir2dbException
     * 
     * @return int
     */
    public function preparedInsertGetCount(string $query, array $params=null): int
    {
        return $this->preparedInsert($query, $params, true);
    }

    /**
     * Returns a PDOStatement object with bound parameters.
     *
     * @param string $query
     * @param array|null $array
     * 
     * @return PDOStatement
     */
    private function prepareStatement(string $query, array $array=null): PDOStatement
    {
        $stmt = $this->dB->prepare($query);
        if ($array) {
            foreach ($array as $key => $value) {
                if (is_numeric($value)) {
                    $stmt->bindParam($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($key, $value, PDO::PARAM_STR);
                }
            }
        }
        return $stmt;
    }

    /**
     * Logs the exception message and trace before throwing a new Dir2dbException.
     *
     * @param PDOException $e
     * 
     * @throws Dir2dbException
     * 
     * @return void
     */
    private function logExceptionBeforeThrowingDir2dbException(PDOException $e): void
    {
        $this->logger->error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        throw new Dir2dbException($e->getMessage(), -1, $e->getPrevious());
    }
}
