<?php

namespace dir2db;

use PDO;
use PDOException;
use stdClass;

/**
 * Class Dataconnection extends Environment. PDO database class.
 */
class DataConnection extends Environment
{
    /**
     * Object of PDO class.
     *
     * @var object
     */
    protected $dB;

    /**
     * Class messages for debugging.
     *
     * @var string
     */
    protected $message;

    /**
     * A stdCLass object for use in type hinted functions.
     *
     * @var stdClass
     */
    private $returnObject;

    public function __construct()
    {
        $GLOBALS = $this->parseIni();
        $this->returnObject = new stdClass;
        $this->dbConnect();
    }
    
    /**
     * Uses the database credentials to establish and set a property containing a PDO connection.
     *
     * @return bool
     */
    private function dbConnect() : bool
    {
        try
        {
            $this->dB = new PDO($GLOBALS['dsn'], $GLOBALS['username'], $GLOBALS['password']);
            $this->dB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        $this->dB->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE); 
            $this->message = 'Database connection success.';
            return TRUE;
        }

        catch(PDOException $error)
        { 
            $this->message = 'Database connection error: '.$error;
            return FALSE;
        }
    }

    /**
     * Safe way to fetch results from a single row using a PDO connection.
     * If return type is TRUE this functions returns an array, otherwise
     * it returns an object of stdClass.
     *
     * @param string $query
     * @param $array
     * @param boolean $returnType
     * @return object, array, or bool
     */
    public function preparedQueryRow(string $query, array $array=NULL, bool $returnType=FALSE)
    {
        try
        {
            $stmt = $this->dB->prepare($query);
            if ($array)
            {
                foreach ($array as $key => $value)
                {
                    if (is_numeric($value))
                    {
                        $stmt->bindParam($key, $value, PDO::PARAM_INT);
                    }
                    else
                    {
                        $stmt->bindParam($key, $value, PDO::PARAM_STR);
                    }
                }
            }   
            if (!$returnType)
            {
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_OBJ);
                return $result;
            }
            else
            {
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result;
            }
        }
        catch (PDOException $error)
        {
            $this->message = 'Database query failed: '.$error;
            if (!$returnType)
            {
                return $this->returnObject->failure = 'check class DataConnection error messages.';
            }
            else
            {
                return array('message' => 'Database query failed.');
            }
        }
    }

    /**
     * Safe way to fetch results from multiple rows using a PDO connection.
     * If return type is TRUE this functions returns an array, otherwise
     * it returns an object of stdClass.
     *
     * @param string $query
     * @param array $array
     * @param boolean $returnType
     * @return object, array or bool
     */
    public function preparedQueryMany(string $query, $array=NULL, bool $returnType=FALSE)
    {
        try
        {
            $stmt = $this->dB->prepare($query);
            if ($array)
            {
                foreach ($array as $key => $value)
                {
                    if (is_numeric($value))
                    {
                        $stmt->bindParam($key, $value, PDO::PARAM_INT);
                    }
                    else
                    {
                        $stmt->bindParam($key, $value, PDO::PARAM_STR);
                    }
                }
            }
            if (!$returnType)
            {
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
                return $result;
            }
            else
            {
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            }
        }
        catch (PDOException $error)
        {
            $this->message = 'Database query failed: '.$error;
            if (!$returnType)
            {
                return self::$returnObject->failure = 'check class Database error message';
            }
            else
            {
                return array('message' => 'Database query failed');
            }
        }
    }

    /**
     * Prepare an INSERT statement with bound parameters, returns bool or error message string.
     * 
     * @example ("INSERT INTO table (column, column, column) VALUES (?, ?, ?)",
     * array('column' => $var, 'column' => $var, 'column' => $var));
     * @param string $query
     * @param array $params
     * @return bool
     * @return integer
     */
    public function preparedInsert(string $query, array $params=NULL, bool $countRequired=FALSE)
    {
        try
        {
            $stmt = $this->dB->prepare($query);
            if ($countRequired)
            {
                $stmt->execute($params);
                return $stmt->rowCount();
            }
            else
            {
                return $stmt->execute($params);
            }
        }
        catch (PDOException $error)
        {
            $this->message = 'Database query failed: '.$error;
            return FALSE;
        }
    }

    /**
     * Piggy backs on the prepared insert function return the affected row count.
     *
     * @param string $query
     * @param array $params
     * @return int
     */
    public function preparedInsertGetCount(string $query, array $params=NULL) : int
    {
        return $this->preparedInsert($query, $params, TRUE);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
