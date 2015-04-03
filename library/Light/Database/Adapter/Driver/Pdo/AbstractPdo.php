<?php

namespace Light\Database\Adapter\Driver\Pdo;

use PDO;
use Light\Database\Adapter\Driver\AbstractDriver;

abstract class AbstractPdo extends AbstractDriver
{

    /**
     * Database driver
     *
     * @var string
     */
    public $dbdriver = 'pdo';

    /**
     * PDO Options
     *
     * @var array
     */
    public $options = array();

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * Validates the DSN string and/or detects the subdriver.
     *
     * @param array $params
     * @return void
     */
    public function __construct($params)
    {
        parent::__construct($params);

        if (preg_match('/([^:]+):/', $this->dsn, $match) && count($match) === 2)
        {
            // If there is a minimum valid dsn string pattern found, we're done
            // This is for general PDO users, who tend to have a full DSN string.
            $this->subdriver = $match[1];
            return;
        }
        // Legacy support for DSN specified in the hostname field
        elseif (preg_match('/([^:]+):/', $this->hostname, $match) && count($match) === 2)
        {
            $this->dsn = $this->hostname;
            $this->hostname = null;
            $this->subdriver = $match[1];
            return;
        }
        elseif (in_array($this->subdriver, array('mssql', 'sybase'), true))
        {
            $this->subdriver = 'dblib';
        }
        elseif ($this->subdriver === '4D')
        {
            $this->subdriver = '4d';
        }
        elseif ( ! in_array($this->subdriver, array('4d', 'cubrid', 'dblib', 'firebird', 'ibm', 'informix', 'mysql', 'oci', 'odbc', 'pgsql', 'sqlite', 'sqlsrv'), true))
        {
            log_message('error', 'PDO: Invalid or non-existent subdriver');

            if ($this->dbDebug)
            {
                showError('Invalid or non-existent PDO subdriver');
            }
        }

        $this->dsn = null;
    }

    /**
     * Database connection
     *
     * @return object
     */
    public function dbConnect()
    {
        $this->options[PDO::ATTR_PERSISTENT] = $this->persistent;

        try {
            return new PDO($this->dsn, $this->username, $this->password, $this->options);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Database version number
     *
     * @return string
     */
    protected function _version()
    {
        try {
            return $this->connId->getAttribute(PDO::ATTR_SERVER_VERSION);
        } catch (\PDOException $e) {
            return 'unkown';//parent::version();
        }
    }

    /**
     * Execute the query
     *
     * @param string $sql SQL query
     * @return mixed
     */
    protected function _execute($sql)
    {
        return $this->connId->query($sql);
    }

    /**
     * Begin Transaction
     *
     * @param bool $testMode
     * @return bool
     */
    public function transBegin($testMode = false)
    {
        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ( ! $this->transEnabled OR $this->_transDepth > 0)
        {
            return true;
        }

        // Reset the transaction failure flag.
        // If the $testMode flag is set to true transactions will be rolled back
        // even if the queries produce a successful result.
        $this->_transFailure = ($testMode === true);

        return $this->connId->beginTransaction();
    }

    /**
     * Commit Transaction
     *
     * @return bool
     */
    public function transCommit()
    {
        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ( ! $this->transEnabled OR $this->_transDepth > 0)
        {
            return true;
        }

        return $this->connId->commit();
    }

    /**
     * Rollback Transaction
     *
     * @return bool
     */
    public function transRollback()
    {
        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ( ! $this->transEnabled OR $this->_transDepth > 0)
        {
            return true;
        }

        return $this->connId->rollBack();
    }

    /**
     * Platform-dependant string escape
     *
     * @param string
     * @return string
     */
    protected function _escapeStr($str)
    {
        // Escape the string
        $str = $this->connId->quote($str);

        // If there are duplicated quotes, trim them away
        return ($str[0] === "'")
            ? substr($str, 1, -1)
            : $str;
    }

    /**
     * Affected Rows
     *
     * @return int
     */
    public function affectedRows()
    {
        return is_object($this->resultId) ? $this->resultId->rowCount() : 0;
    }

    /**
     * Insert ID
     *
     * @param string $name
     * @return int
     */
    public function insertId($name = null)
    {
        return $this->connId->lastInsertId($name);
    }

    /**
     * Field data query
     *
     * Generates a platform-specific query so that the column data can be retrieved
     *
     * @param string $table
     * @return string
     */
    protected function _fieldData($table)
    {
        return 'SELECT TOP 1 * FROM '.$this->protectIdentifiers($table);
    }

    /**
     * Error
     *
     * Returns an array containing code and message of the last
     * database error that has occured.
     *
     * @return array
     */
    public function error()
    {
        $error = array('code' => '00000', 'message' => '');
        $pdoError = $this->connId->errorInfo();

        if (empty($pdoError[0]))
        {
            return $error;
        }

        $error['code'] = isset($pdoError[1]) ? $pdoError[0].'/'.$pdoError[1] : $pdoError[0];
        if (isset($pdoError[2]))
        {
             $error['message'] = $pdoError[2];
        }

        return $error;
    }

    /**
     * Update_Batch statement
     *
     * Generates a platform-specific batch update string from the supplied data
     *
     * @param string $table Table name
     * @param array $values Update data
     * @param string $index WHERE key
     * @return string
     */
    protected function _updateBatch($table, $values, $index)
    {
        $ids = array();
        foreach ($values as $key => $val)
        {
            $ids[] = $val[$index];

            foreach (array_keys($val) as $field)
            {
                if ($field !== $index)
                {
                    $final[$field][] = 'WHEN '.$index.' = '.$val[$index].' THEN '.$val[$field];
                }
            }
        }

        $cases = '';
        foreach ($final as $k => $v)
        {
            $cases .= $k.' = CASE '."\n";

            foreach ($v as $row)
            {
                $cases .= $row."\n";
            }

            $cases .= 'ELSE '.$k.' END, ';
        }

        $this->where($index.' IN('.implode(',', $ids).')', null, false);

        return 'UPDATE '.$table.' SET '.substr($cases, 0, -2).$this->_compileWh('qbWhere');
    }

    /**
     * Truncate statement
     *
     * Generates a platform-specific truncate string from the supplied data
     *
     * If the database does not support the TRUNCATE statement,
     * then this method maps to 'DELETE FROM table'
     *
     * @param string $table
     * @return string
     */
    protected function _truncate($table)
    {
        return 'TRUNCATE TABLE '.$table;
    }
}
