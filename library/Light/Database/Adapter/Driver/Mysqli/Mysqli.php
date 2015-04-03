<?php

namespace Light\Database\Adapter\Driver\Mysqli;

use Light\Database\Adapter\Driver\AbstractDriver;
use Light\Database\DatabaseException;

class Mysqli extends AbstractDriver
{
    /**
     * DELETE hack flag
     *
     * Whether to use the MySQL "delete hack" which allows the number
     * of affected rows to be shown. Uses a preg_replace when enabled,
     * adding a bit more processing to all queries.
     *
     * @var bool
     */
    public $deleteHack = true;

    /**
     * Identifier escape character
     *
     * @var string
     */
    protected $_escapeChar = '`';

    /**
     * Database connection
     *
     * @param bool $persistent
     * @return object
     */
    public function dbConnect()
    {
        if ($this->hostname[0] === '/') {
            $hostname = null;
            $port = null;
            $socket = $this->hostname;
        } else {
            $hostname = ($this->persistent === true && is_php('5.3')) ? 'p:'.$this->hostname : $this->hostname;
            $port = $this->port;
            $socket = null;
        }

        $compress = $this->_getParam('compress');
        $compressClient = $compress === true ? MYSQLI_CLIENT_COMPRESS : 0;

        $mysqli = mysqli_init();
        $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        if ($this->stricton) {
            $mysqli->options(MYSQLI_INIT_COMMAND, 'SET SESSION sql_mode="STRICT_ALL_TABLES"');
        }

        $connect = @$mysqli->real_connect($hostname, $this->username, $this->password, $this->database, $port, $socket, $compressClient);
        $return = empty($connect) ? false : $mysqli;
        return $return;
    }

    /**
     * Reconnect, Keep / reestablish the db connection if no queries have been
     * sent for a length of time exceeding the server's idle timeout
     *
     * @return void
     */
    public function reconnect()
    {
        if ($this->connId !== false && $this->connId->ping() === false) {
            $this->connId = false;
        }
    }

    /**
     * Select the database
     *
     * @param string $database
     * @return bool
     */
    public function selectDb($database = '')
    {
        $return  = false;
        $database = $database === '' ? $this->database : $database;

        if ($this->connId->select_db($database)) {
            $this->database = $database;
            $return = true;
        }

        return $return;
    }

    /**
     * Set client character set
     *
     * @param  string $charSet
     * @return bool
     */
    protected function _charset($charSet)
    {
        return $this->connId->set_charset($charSet);
    }

    /**
     * Database version number
     *
     * @return string
     */
    protected function _version()
    {
        return $this->dataCache['version'] = $this->connId->server_info;
    }

    /**
     * Execute the query
     *
     * @param string $sql an SQL query
     * @return mixed
     */
    protected function _execute($sql)
    {
        return $this->connId->query($this->_prepQuery($sql));
    }

    /**
     * Prep the query, If needed, each database adapter can prep the query string
     * mysqli_affected_rows() returns 0 for "DELETE FROM TABLE" queries. This hack
     * modifies the query so that it a proper number of affected rows is returned.
     *
     * @param string $sql an SQL query
     * @return string
     */
    protected function _prepQuery($sql)
    {
        if ($this->deleteHack === true && preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
            return trim($sql).' WHERE 1=1';
        }

        return $sql;
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
        if ( ! $this->transEnabled OR $this->_transDepth > 0) {
            return true;
        }

        // Reset the transaction failure flag.
        // If the $testMode flag is set to true transactions will be rolled back
        // even if the queries produce a successful result.
        $this->_transFailure = ($testMode === true);

        $this->connId->autocommit(false);
        return is_php('5.5')
            ? $this->connId->begin_transaction()
            : $this->simpleQuery('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
    }

    /**
     * Commit Transaction
     *
     * @return bool
     */
    public function transCommit()
    {
        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ( ! $this->transEnabled OR $this->_transDepth > 0) {
            return true;
        }

        if ($this->connId->commit()) {
            $this->connId->autocommit(true);
            return true;
        }

        return false;
    }

    /**
     * Rollback Transaction
     *
     * @return bool
     */
    public function transRollback()
    {
        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ( ! $this->transEnabled OR $this->_transDepth > 0) {
            return true;
        }

        if ($this->connId->rollback()) {
            $this->connId->autocommit(true);
            return true;
        }

        return false;
    }

    /**
     * Platform-dependant string escape
     *
     * @param string
     * @return string
     */
    protected function _escapeStr($str)
    {
        return $this->connId->real_escape_string($str);
    }

    /**
     * Affected Rows
     *
     * @return int
     */
    public function affectedRows()
    {
        return $this->connId->affected_rows;
    }

    /**
     * Insert ID
     *
     * @return int
     */
    public function insertId()
    {
        return $this->connId->insert_id;
    }

    /**
     * List table query, Generates a platform-specific query string so that the table names can be fetched
     *
     * @param bool $prefixLimit
     * @return string
     */
    protected function _listTables($prefixLimit = false)
    {
        $sql = 'SHOW TABLES FROM '.$this->escapeIdentifiers($this->database);

        if ($prefixLimit !== false && $this->dbprefix !== '')
        {
            return $sql." LIKE '".$this->escapeLikeStr($this->dbprefix)."%'";
        }

        return $sql;
    }

    /**
     * Show column query, Generates a platform-specific query string so that
     * the column names can be fetched
     *
     * @param string $table
     * @return string
     */
    protected function _listColumns($table = '')
    {
        return 'SHOW COLUMNS FROM '.$this->protectIdentifiers($table, true, null, false);
    }

    /**
     * Returns an object with field data
     *
     * @param string $table
     * @return array
     */
    public function fieldData($table)
    {
        if (($query = $this->query('SHOW COLUMNS FROM '.$this->protectIdentifiers($table, true, null, false))) === false) {
            return false;
        }
        $query = $query->result_object();

        $retval = array();
        for ($i = 0, $c = count($query); $i < $c; $i++) {
            $retval[$i]            = new stdClass();
            $retval[$i]->name        = $query[$i]->Field;

            sscanf($query[$i]->Type, '%[a-z](%d)',
                $retval[$i]->type,
                $retval[$i]->maxLength
            );

            $retval[$i]->default        = $query[$i]->Default;
            $retval[$i]->primaryKey    = (int) ($query[$i]->Key === 'PRI');
        }

        return $retval;
    }

    /**
     * Error, Returns an array containing code and message of the last
     * database error that has occurred.
     *
     * @return array
     */
    public function error()
    {
        if (!empty($this->connId->connect_errno)) {
            return array(
                'code' => $this->connId->connect_errno,
                'message' => is_php('5.2.9') ? $this->connId->connect_error : mysqli_connect_error()
            );
        }

        return array('code' => $this->connId->errno, 'message' => $this->connId->error);
    }

    /**
     * FROM tables, Groups tables in FROM clauses if needed, 
     * so there is no confusion about operator precedence.
     *
     * @return string
     */
    protected function _fromTables()
    {
        if ( ! empty($this->qbJoin) && count($this->qbFrom) > 1) {
            return '('.implode(', ', $this->qbFrom).')';
        }

        return implode(', ', $this->qbFrom);
    }

    /**
     * Close DB Connection
     *
     * @return void
     */
    protected function _close()
    {
        $this->connId->close();
    }
}
