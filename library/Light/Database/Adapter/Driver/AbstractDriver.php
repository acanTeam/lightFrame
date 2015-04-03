<?php

namespace Light\Database\Adapter\Driver;

use Light\Database\DatabaseException;
use Light\Database\Cache;

abstract class AbstractDriver
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @var bool
     */
    public $connId            = false;

    public $dataCache        = array();

    public $cache;
    /*
    public $dbprefix        = '';
    public $dbcollat        = 'utf8_generalCi';
    public $encrypt            = false;
    public $swapPre        = '';
    public $port            = '';
    public $pconnect        = false;

    public $resultId        = false;
    public $dbDebug        = false;
    public $benchmark        = 0;
    public $queryCount        = 0;
    public $bindMarker        = '?';
    public $saveQueries        = true;
    public $queries            = array();
    public $queryTimes        = array();
    public $transEnabled        = true;
    public $transStrict        = true;
    protected $_transDepth        = 0;
    protected $_transStatus    = true;
    protected $_transFailure    = false;
    public $cachedir        = '';
    public $cacheAutodel        = false;
    protected $_protectIdentifiers        = true;
    protected $_reservedIdentifiers    = array('*');
    protected $_escapeChar = '"';
    protected $_likeEscapeStr = " ESCAPE '%s' ";
    protected $_likeEscapeChr = '!';
    protected $_randomKeyword = array('RAND()', 'RAND(%d)');
    protected $_countString = 'SELECT COUNT(*) AS ';*/

    /**
     * constructor
     *
     * @param array $params
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;

        //logMessage('info', 'Database Driver Class Initialized');
        $this->_initialize();
    }

    protected function _initialize()
    {
        // Already connected
        if ($this->connId) {
            return true;
        }

        $this->connId = $this->dbConnect($this->params);

        if (!$this->connId && isset($this->params['failover']) && !empty($this->params['failover'])) {
            foreach ((array) $this->params['failover'] as $failover) {
                $this->params = $failover;
                $this->connId = $this->dbConnect();
                if (!empty($this->connId)) {
                    break;
                }
            }
        }

        if (empty($this->connId)) {
            throw new DatabaseException('Unable to connect to the database');
        }

        $charSet = $this->_getParam('char_set', 'utf8');
        $this->_setCharSet($this->charSet);
    }

    /**
     * DB connect This is just a dummy method that all drivers will override.
     *
     * @return      mixed
     */
    abstract public function dbConnect();

    /**
     * Reconnect Keep / reestablish the db connection if no queries have been
     * sent for a length of time exceeding the server's idle timeout.
     *
     * @return      void
     */
    //abstract public function reconnect();

    /**
     * Select database
     *
     * @return      bool
     */
    //abstract public function selectDb();

    /**
     * Set client character set
     *
     * @param string
     * @return bool
     */
    protected function _setCharSet($charSet)
    {
        if (method_exists($this, '_charSet')) {
           $this->_charSet($charSet);
        }
    }

    /**
     * The name of the platform in use (mysql, mssql, etc...)
     *
     * @return string
     */
    public function platform()
    {
        return $this->dbdriver;
    }

    /**
     * Database version number Returns a string containing the version of the database being used.
     * Most drivers will override this method.
     *
     * @return string
     */
    public function version()
    {
        if (!isset($this->dataCache['version'])) {
            $version = method_exists($this, '_setCharSet') ? $this->_version() : 'unknow';
            $this->dataCache['version'] = $version;
        }

        return $this->dataCache['version'];
    }

    /**
     * Version number query string
     *
     * @return string
     */
    protected function _version()
    {
        return 'SELECT VERSION() AS ver';
    }

    // --------------------------------------------------------------------

    /**
     * Execute the query
     *
     * Accepts an SQL string as input and returns a result object upon
     * successful execution of a "read" type query. Returns boolean true
     * upon successful execution of a "write" type query. Returns boolean
     * false upon failure, and if the $dbDebug variable is set to true
     * will raise an error.
     *
     * @param string $sql
     * @param array $binds = false        An array of binding data
     * @param bool $returnObject = null
     * @return mixed
     */
    public function query($sql, $binds = false, $returnObject = null)
    {
        if ($sql === '')
        {
            logMessage('error', 'Invalid query: '.$sql);
            return ($this->dbDebug) ? $this->displayError('dbInvalidQuery') : false;
        }
        elseif ( ! isBool($returnObject))
        {
            $returnObject = ! $this->isWriteType($sql);
        }

        // Verify table prefix and replace if necessary
        if ($this->dbprefix !== '' && $this->swapPre !== '' && $this->dbprefix !== $this->swapPre)
        {
            $sql = pregReplace('/(\W)'.$this->swapPre.'(\S+?)/', '\\1'.$this->dbprefix.'\\2', $sql);
        }

        // Compile binds if needed
        if ($binds !== false)
        {
            $sql = $this->compileBinds($sql, $binds);
        }

        // Is query caching enabled? If the query is a "read type"
        // we will load the caching class and return the previously
        // cached query if it exists
        if ($this->cacheOn === true && $returnObject === true && $this->_getCache()) {
            $this->loadRdriver();
            if (false !== ($cache = $this->cache->read($sql)))
            {
                return $cache;
            }
        }

        // Save the query for debugging
        if ($this->saveQueries === true)
        {
            $this->queries[] = $sql;
        }

        // Start the Query Timer
        $timeStart = microtime(true);

        // Run the Query
        if (false === ($this->resultId = $this->simpleQuery($sql)))
        {
            if ($this->saveQueries === true)
            {
                $this->queryTimes[] = 0;
            }

            // This will trigger a rollback if transactions are being used
            $this->_transStatus = false;

            // Grab the error now, as we might run some additional queries before displaying the error
            $error = $this->error();

            // Log errors
            logMessage('error', 'Query error: '.$error['message'].' - Invalid query: '.$sql);

            if ($this->dbDebug)
            {
                // We call this function in order to roll-back queries
                // if transactions are enabled. If we don't call this here
                // the error message will trigger an exit, causing the
                // transactions to remain in limbo.
                if ($this->_transDepth !== 0)
                {
                    do
                    {
                        $this->transComplete();
                    }
                    while ($this->_transDepth !== 0);
                }

                // Display errors
                return $this->displayError(array('Error Number: '.$error['code'], $error['message'], $sql));
            }

            return false;
        }

        // Stop and aggregate the query time results
        $timeEnd = microtime(true);
        $this->benchmark += $timeEnd - $timeStart;

        if ($this->saveQueries === true)
        {
            $this->queryTimes[] = $timeEnd - $timeStart;
        }

        // Increment the query counter
        $this->queryCount++;

        // Will we have a result object instantiated? If not - we'll simply return true
        if ($returnObject !== true)
        {
            // If caching is enabled we'll auto-cleanup any existing files related to this particular URI
            if ($this->cacheOn === true && $this->cacheAutodel === true && $this->_getCache())
            {
                $this->cache->delete();
            }

            return true;
        }

        // Load and instantiate the result driver
        $driver        = $this->loadRdriver();
        $RES        = new $driver($this);

        // Is query caching enabled? If so, we'll serialize the
        // result object and save it to a cache file.
        if ($this->cacheOn === true && $this->_getCache())
        {
            // We'll create a new instance of the result object
            // only without the platform specific driver since
            // we can't use it with cached data (the query result
            // resource ID won't be any good once we've cached the
            // result object, so we'll have to compile the data
            // and save it)
            $CR = new CI_DB_result($this);
            $CR->resultObject    = $RES->resultObject();
            $CR->resultArray    = $RES->resultArray();
            $CR->numRows        = $RES->numRows();

            // Reset these since cached objects can not utilize resource IDs.
            $CR->connId        = null;
            $CR->resultId        = null;

            $this->cache->write($sql, $CR);
        }

        return $RES;
    }

    // --------------------------------------------------------------------

    /**
     * Load the result drivers
     *
     * @return string the name of the result class
     */
    public function loadRdriver()
    {
        $driver = 'CI_DB_'.$this->dbdriver.'_result';

        if ( ! classExists($driver, false))
        {
            requireOnce(BASEPATH.'database/DB_result.php');
            requireOnce(BASEPATH.'database/drivers/'.$this->dbdriver.'/'.$this->dbdriver.'_result.php');
        }

        return $driver;
    }

    // --------------------------------------------------------------------

    /**
     * Simple Query
     * This is a simplified version of the query() function. Internally
     * we only use it when running transaction commands since they do
     * not require all the features of the main query() function.
     *
     * @param string the sql query
     * @return mixed
     */
    public function simpleQuery($sql)
    {
        if ( ! $this->connId)
        {
            $this->initialize();
        }

        return $this->_execute($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Disable Transactions
     * This permits transactions to be disabled at run-time.
     *
     * @return void
     */
    public function transOff()
    {
        $this->transEnabled = false;
    }

    // --------------------------------------------------------------------

    /**
     * Enable/disable Transaction Strict Mode
     * When strict mode is enabled, if you are running multiple groups of
     * transactions, if one group fails all groups will be rolled back.
     * If strict mode is disabled, each group is treated autonomously, meaning
     * a failure of one group will not affect any others
     *
     * @param bool $mode = true
     * @return void
     */
    public function transStrict($mode = true)
    {
        $this->transStrict = isBool($mode) ? $mode : true;
    }

    // --------------------------------------------------------------------

    /**
     * Start Transaction
     *
     * @param bool $testMode = false
     * @return void
     */
    public function transStart($testMode = false)
    {
        if ( ! $this->transEnabled)
        {
            return;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_transDepth > 0)
        {
            $this->_transDepth += 1;
            return;
        }

        $this->transBegin($testMode);
        $this->_transDepth += 1;
    }

    // --------------------------------------------------------------------

    /**
     * Complete Transaction
     *
     * @return bool
     */
    public function transComplete()
    {
        if ( ! $this->transEnabled)
        {
            return false;
        }

        // When transactions are nested we only begin/commit/rollback the outermost ones
        if ($this->_transDepth > 1)
        {
            $this->_transDepth -= 1;
            return true;
        }
        else
        {
            $this->_transDepth = 0;
        }

        // The query() function will set this flag to false in the event that a query failed
        if ($this->_transStatus === false OR $this->_transFailure === true)
        {
            $this->transRollback();

            // If we are NOT running in strict mode, we will reset
            // the _transStatus flag so that subsequent groups of transactions
            // will be permitted.
            if ($this->transStrict === false)
            {
                $this->_transStatus = true;
            }

            logMessage('debug', 'DB Transaction Failure');
            return false;
        }

        $this->transCommit();
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Lets you retrieve the transaction flag to determine if it has failed
     *
     * @return bool
     */
    public function transStatus()
    {
        return $this->_transStatus;
    }

    // --------------------------------------------------------------------

    /**
     * Compile Bindings
     *
     * @param string the sql statement
     * @param array an array of bind data
     * @return string
     */
    public function compileBinds($sql, $binds)
    {
        if (empty($binds) OR empty($this->bindMarker) OR strpos($sql, $this->bindMarker) === false)
        {
            return $sql;
        }
        elseif ( ! isArray($binds))
        {
            $binds = array($binds);
            $bindCount = 1;
        }
        else
        {
            // Make sure we're using numeric keys
            $binds = array_values($binds);
            $bindCount = count($binds);
        }

        // We'll need the marker length later
        $ml = strlen($this->bindMarker);

        // Make sure not to replace a chunk inside a string that happens to match the bind marker
        if ($c = pregMatchAll("/'[^']*'/i", $sql, $matches))
        {
            $c = pregMatchAll('/'.pregQuote($this->bindMarker, '/').'/i',
                strReplace($matches[0],
                    strReplace($this->bindMarker, strRepeat(' ', $ml), $matches[0]),
                    $sql, $c),
                $matches, PREG_OFFSET_CAPTURE);

            // Bind values' count must match the count of markers in the query
            if ($bindCount !== $c)
            {
                return $sql;
            }
        }
        elseif (($c = pregMatchAll('/'.pregQuote($this->bindMarker, '/').'/i', $sql, $matches, PREG_OFFSET_CAPTURE)) !== $bindCount)
        {
            return $sql;
        }

        do
        {
            $c--;
            $escapedValue = $this->escape($binds[$c]);
            if (isArray($escapedValue))
            {
                $escapedValue = '('.implode(',', $escapedValue).')';
            }
            $sql = substrReplace($sql, $escapedValue, $matches[0][$c][1], $ml);
        }
        while ($c !== 0);

        return $sql;
    }

    /**
     * Determines if a query is a "write" type.
     *
     * @param string An SQL query string
     * @return bool
     */
    public function isWriteType($sql)
    {
        $pattern = '/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX)\s/i';
        return (bool) preg_match($pattern, $sql);
    }

    /**
     * Calculate the aggregate query elapsed time
     *
     * @param int The number of decimal places
     * @return string
     */
    public function elapsedTime($decimals = 6)
    {
        return number_format($this->benchmark, $decimals);
    }

    /**
     * Returns the total number of queries
     *
     * @return int
     */
    public function totalQueries()
    {
        return $this->queryCount;
    }

    /**
     * Returns the last query that was executed
     *
     * @return string
     */
    public function lastQuery()
    {
        return end($this->queries);
    }

    /**
     * "Smart" Escape String
     *
     * Escapes data based on type
     * Sets boolean and null types
     *
     * @param string
     * @return mixed
     */
    public function escape($str)
    {
        if (is_array($str)) {
            $str = array_map(array(&$this, 'escape'), $str);
            return $str;
        } elseif (is_string($str) OR (is_object($str) && method_exists($str, '__toString'))) {
            return "'".$this->escapeStr($str)."'";
        } elseif (is_bool($str)) {
            return ($str === false) ? 0 : 1;
        } elseif ($str === null) {
            return 'null';
        }

        return $str;
    }

    /**
     * Escape String
     *
     * @param string|string[]    $str Input string
     * @param bool $like Whether or not the string will be used in a LIKE condition
     * @return string
     */
    public function escapeStr($str, $like = false)
    {
        if (isArray($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->escapeStr($val, $like);
            }

            return $str;
        }

        $str = $this->_escapeStr($str);

        // escape LIKE condition wildcards
        if ($like === true) {
            return strReplace(
                array($this->_likeEscapeChr, '%', '_'),
                array($this->_likeEscapeChr.$this->_likeEscapeChr, $this->_likeEscapeChr.'%', $this->_likeEscapeChr.'_'),
                $str
            );
        }

        return $str;
    }

    /**
     * Escape LIKE String
     *
     * Calls the individual driver for platform
     * specific escaping for LIKE conditions
     *
     * @param string|string[]
     * @return mixed
     */
    public function escapeLikeStr($str)
    {
        return $this->escapeStr($str, true);
    }

    /**
     * Platform-dependant string escape
     *
     * @param string
     * @return string
     */
    protected function _escapeStr($str)
    {
        return str_replace("'", "''", removeInvisibleCharacters($str));
    }

    /**
     * Primary
     *
     * Retrieves the primary key. It assumes that the row in the first
     * position is the primary key
     *
     * @param string $table Table name
     * @return string
     */
    public function primary($table)
    {
        $fields = $this->listFields($table);
        return is_array($fields) ? current($fields) : false;
    }

    /**
     * "Count All" query
     *
     * Generates a platform-specific query string that counts all records in
     * the specified database
     *
     * @param string
     * @return int
     */
    public function countAll($table = '')
    {
        if ($table === '') {
            return 0;
        }

        $query = $this->query($this->_countString.$this->escapeIdentifiers('numrows').' FROM '.$this->protectIdentifiers($table, true, null, false));
        if ($query->numRows() === 0) {
            return 0;
        }

        $query = $query->row();
        $this->_resetSelect();
        return (int) $query->numrows;
    }

    /**
     * Returns an array of table names
     *
     * @param string $constrainByPrefix = false
     * @return array
     */
    public function listTables($constrainByPrefix = false)
    {
        // Is there a cached result?
        if (isset($this->dataCache['tableNames'])) {
            return $this->dataCache['tableNames'];
        }

        if (false === ($sql = $this->_listTables($constrainByPrefix))) {
            return ($this->dbDebug) ? $this->displayError('dbUnsupportedFunction') : false;
        }

        $this->dataCache['tableNames'] = array();
        $query = $this->query($sql);

        foreach ($query->resultArray() as $row) {
            // Do we know from which column to get the table name?
            if ( ! isset($key)) {
                if (isset($row['tableName'])) {
                    $key = 'tableName';
                } elseif (isset($row['TABLE_NAME'])) {
                    $key = 'TABLE_NAME';
                } else {
                    /* We have no other choice but to just get the first element's key.
                     * Due to array_shift() accepting its argument by reference, if
                     * E_STRICT is on, this would trigger a warning. So we'll have to
                     * assign it first.
                     */
                    $key = array_keys($row);
                    $key = array_shift($key);
                }
            }

            $this->dataCache['tableNames'][] = $row[$key];
        }

        return $this->dataCache['tableNames'];
    }

    // --------------------------------------------------------------------

    /**
     * Determine if a particular table exists
     *
     * @param string $tableName
     * @return bool
     */
    public function tableExists($tableName)
    {
        return inArray($this->protectIdentifiers($tableName, true, false, false), $this->listTables());
    }

    // --------------------------------------------------------------------

    /**
     * Fetch Field Names
     *
     * @param string the table name
     * @return array
     */
    public function listFields($table)
    {
        // Is there a cached result?
        if (isset($this->dataCache['fieldNames'][$table]))
        {
            return $this->dataCache['fieldNames'][$table];
        }

        if (false === ($sql = $this->_listColumns($table)))
        {
            return ($this->dbDebug) ? $this->displayError('dbUnsupportedFunction') : false;
        }

        $query = $this->query($sql);
        $this->dataCache['fieldNames'][$table] = array();

        foreach ($query->resultArray() as $row)
        {
            // Do we know from where to get the column's name?
            if ( ! isset($key))
            {
                if (isset($row['columnName']))
                {
                    $key = 'columnName';
                }
                elseif (isset($row['COLUMN_NAME']))
                {
                    $key = 'COLUMN_NAME';
                }
                else
                {
                    // We have no other choice but to just get the first element's key.
                    $key = key($row);
                }
            }

            $this->dataCache['fieldNames'][$table][] = $row[$key];
        }

        return $this->dataCache['fieldNames'][$table];
    }

    // --------------------------------------------------------------------

    /**
     * Determine if a particular field exists
     *
     * @param string
     * @param string
     * @return bool
     */
    public function fieldExists($fieldName, $tableName)
    {
        return inArray($fieldName, $this->listFields($tableName));
    }

    /**
     * Returns an object with field data
     *
     * @param string $table the table name
     * @return array
     */
    public function fieldData($table)
    {
        $query = $this->query($this->_fieldData($this->protectIdentifiers($table, true, null, false)));
        return ($query) ? $query->fieldData() : false;
    }

    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     *
     * @param mixed
     * @return mixed
     */
    public function escapeIdentifiers($item)
    {
        if ($this->_escapeChar === '' OR empty($item) OR inArray($item, $this->_reservedIdentifiers)) {
            return $item;
        } elseif (isArray($item)) {
            foreach ($item as $key => $value) {
                $item[$key] = $this->escapeIdentifiers($value);
            }

            return $item;
        } elseif (ctypeDigit($item) OR $item[0] === "'" OR ($this->_escapeChar !== '"' && $item[0] === '"') OR strpos($item, '(') !== false) {
            // Avoid breaking functions and literal values inside queries
            return $item;
        }

        static $pregEc = array();

        if (empty($pregEc)) {
            if (isArray($this->_escapeChar)) {
                $pregEc = array(
                    pregQuote($this->_escapeChar[0], '/'),
                    pregQuote($this->_escapeChar[1], '/'),
                    $this->_escapeChar[0],
                    $this->_escapeChar[1]
                );
            } else {
                $pregEc[0] = $pregEc[1] = pregQuote($this->_escapeChar, '/');
                $pregEc[2] = $pregEc[3] = $this->_escapeChar;
            }
        }

        foreach ($this->_reservedIdentifiers as $id) {
            if (strpos($item, '.'.$id) !== false) {
                return pregReplace('/'.$pregEc[0].'?([^'.$pregEc[1].'\.]+)'.$pregEc[1].'?\./i', $pregEc[2].'$1'.$pregEc[3].'.', $item);
            }
        }

        return pregReplace('/'.$pregEc[0].'?([^'.$pregEc[1].'\.]+)'.$pregEc[1].'?(\.)?/i', $pregEc[2].'$1'.$pregEc[3].'$2', $item);
    }

    /**
     * Generate an insert string
     *
     * @param string the table upon which the query will be performed
     * @param array an associative array data of key/values
     * @return string
     */
    public function insertString($table, $data)
    {
        $fields = $values = array();

        foreach ($data as $key => $val) {
            $fields[] = $this->escapeIdentifiers($key);
            $values[] = $this->escape($val);
        }

        return $this->_insert($this->protectIdentifiers($table, true, null, false), $fields, $values);
    }

    /**
     * Insert statement
     *
     * Generates a platform-specific insert string from the supplied data
     *
     * @param string the table name
     * @param array the insert keys
     * @param array the insert values
     * @return string
     */
    protected function _insert($table, $keys, $values)
    {
        return 'INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
    }

    /**
     * Generate an update string
     *
     * @param string the table upon which the query will be performed
     * @param array an associative array data of key/values
     * @param mixed the "where" statement
     * @return string
     */
    public function updateString($table, $data, $where)
    {
        if (empty($where)) {
            return false;
        }

        $this->where($where);

        $fields = array();
        foreach ($data as $key => $val) {
            $fields[$this->protectIdentifiers($key)] = $this->escape($val);
        }

        $sql = $this->_update($this->protectIdentifiers($table, true, null, false), $fields);
        $this->_resetWrite();
        return $sql;
    }

    /**
     * Update statement
     *
     * Generates a platform-specific update string from the supplied data
     *
     * @param string the table name
     * @param array the update data
     * @return string
     */
    protected function _update($table, $values)
    {
        foreach ($values as $key => $val) {
            $valstr[] = $key.' = '.$val;
        }

        return 'UPDATE '.$table.' SET '.implode(', ', $valstr)
            .$this->_compileWh('qbWhere')
            .$this->_compileOrderBy()
            .($this->qbLimit ? ' LIMIT '.$this->qbLimit : '');
    }

    /**
     * Tests whether the string has an SQL operator
     *
     * @param string
     * @return bool
     */
    protected function _hasOperator($str)
    {
        return (bool) pregMatch('/(<|>|!|=|\sIS null|\sIS NOT null|\sEXISTS|\sBETWEEN|\sLIKE|\sIN\s*\(|\s)/i', trim($str));
    }

    /**
     * Returns the SQL string operator
     *
     * @param string
     * @return string
     */
    protected function _getOperator($str)
    {
        static $_operators;

        if (empty($_operators)) {
            $_les = ($this->_likeEscapeStr !== '')
                ? '\s+'.pregQuote(trim(sprintf($this->_likeEscapeStr, $this->_likeEscapeChr)), '/')
                : '';
            $_operators = array(
                '\s*(?:<|>|!)?=\s*',        // =, <=, >=, !=
                '\s*<>?\s*',            // <, <>
                '\s*>\s*',            // >
                '\s+IS null',            // IS null
                '\s+IS NOT null',        // IS NOT null
                '\s+EXISTS\s*\([^\)]+\)',    // EXISTS(sql)
                '\s+NOT EXISTS\s*\([^\)]+\)',    // NOT EXISTS(sql)
                '\s+BETWEEN\s+\S+\s+AND\s+\S+',    // BETWEEN value AND value
                '\s+IN\s*\([^\)]+\)',        // IN(list)
                '\s+NOT IN\s*\([^\)]+\)',    // NOT IN (list)
                '\s+LIKE\s+\S+'.$_les,        // LIKE 'expr'[ ESCAPE '%s']
                '\s+NOT LIKE\s+\S+'.$_les    // NOT LIKE 'expr'[ ESCAPE '%s']
            );

        }

        return pregMatch('/'.implode('|', $_operators).'/i', $str, $match)
            ? $match[0] : false;
    }

    /**
     * Enables a native PHP function to be run, using a platform agnostic wrapper.
     *
     * @param string $function Function name
     * @return mixed
     */
    public function callFunction($function)
    {
        $driver = ($this->dbdriver === 'postgre') ? 'pg_' : $this->dbdriver.'_';

        if (false === strpos($driver, $function)) {
            $function = $driver.$function;
        }

        if ( ! functionExists($function)) {
            return ($this->dbDebug) ? $this->displayError('dbUnsupportedFunction') : false;
        }

        return (funcNumArgs() > 1)
            ? callUserFuncArray($function, array_slice(funcGetArgs(), 1))
            : callUserFunc($function);
    }

    /**
     * Set Cache Directory Path
     *
     * @param string the path to the cache directory
     * @return void
     */
    public function cacheSetPath($path = '')
    {
        $this->cachedir = $path;
    }

    /**
     * Delete the cache files associated with a particular URI
     *
     * @param string $segmentOne = ''
     * @param string $segmentTwo = ''
     * @return bool
     */
    public function cacheDelete($segmentOne = '', $segmentTwo = '')
    {
        return $this->_getCache()->delete($segmentOne, $segmentTwo);
    }

    /**
     * Delete All cache files
     *
     * @return bool
     */
    public function cacheDeleteAll()
    {
        return $this->_getCache()->deleteAll();
    }

    /**
     * Initialize the Cache Class
     *
     * @return bool
     */
    protected function _getCache()
    {
        if (empty($this->cache) || !is_object(Cache)) {
            $this->cache = new Cache($this); 
        }

        return $this->cache;
    }

    /**
     * Close DB Connection
     *
     * @return void
     */
    public function close()
    {
        if ($this->connId) {
            $this->_close();
            $this->connId = false;
        }
    }

    /**
     * Close DB Connection, This method would be overridden by most of the drivers.
     *
     * @return void
     */
    abstract protected function _close();

    /**
     * Display an error message
     *
     * @param string the error message
     * @param string any "swap" values
     * @param bool whether to localize the message
     * @return string sends the application/views/errors/errorDb.php template
     */
    public function displayError($error = '', $swap = '', $native = false)
    {
        $LANG =& loadClass('Lang', 'core');
        $LANG->load('db');

        $heading = $LANG->line('dbErrorHeading');

        if ($native === true)
        {
            $message = (array) $error;
        }
        else
        {
            $message = isArray($error) ? $error : array(strReplace('%s', $swap, $LANG->line($error)));
        }

        // Find the most likely culprit of the error by going through
        // the backtrace until the source file is no longer in the
        // database folder.
        $trace = debugBacktrace();
        foreach ($trace as $call)
        {
            if (isset($call['file'], $call['class']))
            {
                // We'll need this on Windows, as APPPATH and BASEPATH will always use forward slashes
                if (DIRECTORY_SEPARATOR !== '/')
                {
                    $call['file'] = strReplace('\\', '/', $call['file']);
                }

                if (strpos($call['file'], BASEPATH.'database') === false && strpos($call['class'], 'Loader') === false)
                {
                    // Found it - use a relative path for safety
                    $message[] = 'Filename: '.strReplace(array(APPPATH, BASEPATH), '', $call['file']);
                    $message[] = 'Line Number: '.$call['line'];
                    break;
                }
            }
        }

        $error =& loadClass('DatabaseExceptions', 'core');
        echo $error->showError($heading, $message, 'errorDb');
        exit(8); // EXIT_DATABASE
    }

    /**
     * Protect Identifiers
     *
     * This function is used extensively by the Query Builder class, and by
     * a couple functions in this class.
     * It takes a column or table name (optionally with an alias) and inserts
     * the table prefix onto it. Some logic is necessary in order to deal with
     * column names that include the path. Consider a query like this:
     *
     * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
     *
     * Or a query with aliasing:
     *
     * SELECT m.memberId, m.memberName FROM members AS m
     *
     * Since the column name can include up to four segments (host, DB, table, column)
     * or also have an alias prefix, we need to do a bit of work to figure this out and
     * insert the table prefix (if it exists) in the proper position, and escape only
     * the correct identifiers.
     *
     * @param string
     * @param bool
     * @param mixed
     * @param bool
     * @return string
     */
    public function protectIdentifiers($item, $prefixSingle = false, $protectIdentifiers = null, $fieldExists = true)
    {
        if ( ! isBool($protectIdentifiers))
        {
            $protectIdentifiers = $this->_protectIdentifiers;
        }

        if (isArray($item))
        {
            $escapedArray = array();
            foreach ($item as $k => $v)
            {
                $escapedArray[$this->protectIdentifiers($k)] = $this->protectIdentifiers($v, $prefixSingle, $protectIdentifiers, $fieldExists);
            }

            return $escapedArray;
        }

        // This is basically a bug fix for queries that use MAX, MIN, etc.
        // If a parenthesis is found we know that we do not need to
        // escape the data or add a prefix. There's probably a more graceful
        // way to deal with this, but I'm not thinking of it -- Rick
        //
        // Added exception for single quotes as well, we don't want to alter
        // literal strings. -- Narf
        if (strpos($item, '(') !== false OR strpos($item, "'") !== false)
        {
            return $item;
        }

        // Convert tabs or multiple spaces into single spaces
        $item = pregReplace('/\s+/', ' ', $item);

        // If the item has an alias declaration we remove it and set it aside.
        // Note: strripos() is used in order to support spaces in table names
        if ($offset = strripos($item, ' AS '))
        {
            $alias = ($protectIdentifiers)
                ? substr($item, $offset, 4).$this->escapeIdentifiers(substr($item, $offset + 4))
                : substr($item, $offset);
            $item = substr($item, 0, $offset);
        }
        elseif ($offset = strrpos($item, ' '))
        {
            $alias = ($protectIdentifiers)
                ? ' '.$this->escapeIdentifiers(substr($item, $offset + 1))
                : substr($item, $offset);
            $item = substr($item, 0, $offset);
        }
        else
        {
            $alias = '';
        }

        // Break the string apart if it contains periods, then insert the table prefix
        // in the correct location, assuming the period doesn't indicate that we're dealing
        // with an alias. While we're at it, we will escape the components
        if (strpos($item, '.') !== false)
        {
            $parts    = explode('.', $item);

            // Does the first segment of the exploded item match
            // one of the aliases previously identified? If so,
            // we have nothing more to do other than escape the item
            if (inArray($parts[0], $this->qbAliasedTables))
            {
                if ($protectIdentifiers === true)
                {
                    foreach ($parts as $key => $val)
                    {
                        if ( ! inArray($val, $this->_reservedIdentifiers))
                        {
                            $parts[$key] = $this->escapeIdentifiers($val);
                        }
                    }

                    $item = implode('.', $parts);
                }

                return $item.$alias;
            }

            // Is there a table prefix defined in the config file? If not, no need to do anything
            if ($this->dbprefix !== '')
            {
                // We now add the table prefix based on some logic.
                // Do we have 4 segments (hostname.database.table.column)?
                // If so, we add the table prefix to the column name in the 3rd segment.
                if (isset($parts[3]))
                {
                    $i = 2;
                }
                // Do we have 3 segments (database.table.column)?
                // If so, we add the table prefix to the column name in 2nd position
                elseif (isset($parts[2]))
                {
                    $i = 1;
                }
                // Do we have 2 segments (table.column)?
                // If so, we add the table prefix to the column name in 1st segment
                else
                {
                    $i = 0;
                }

                // This flag is set when the supplied $item does not contain a field name.
                // This can happen when this function is being called from a JOIN.
                if ($fieldExists === false)
                {
                    $i++;
                }

                // Verify table prefix and replace if necessary
                if ($this->swapPre !== '' && strpos($parts[$i], $this->swapPre) === 0)
                {
                    $parts[$i] = pregReplace('/^'.$this->swapPre.'(\S+?)/', $this->dbprefix.'\\1', $parts[$i]);
                }
                // We only add the table prefix if it does not already exist
                elseif (strpos($parts[$i], $this->dbprefix) !== 0)
                {
                    $parts[$i] = $this->dbprefix.$parts[$i];
                }

                // Put the parts back together
                $item = implode('.', $parts);
            }

            if ($protectIdentifiers === true)
            {
                $item = $this->escapeIdentifiers($item);
            }

            return $item.$alias;
        }

        // Is there a table prefix? If not, no need to insert it
        if ($this->dbprefix !== '')
        {
            // Verify table prefix and replace if necessary
            if ($this->swapPre !== '' && strpos($item, $this->swapPre) === 0)
            {
                $item = pregReplace('/^'.$this->swapPre.'(\S+?)/', $this->dbprefix.'\\1', $item);
            }
            // Do we prefix an item with no segments?
            elseif ($prefixSingle === true && strpos($item, $this->dbprefix) !== 0)
            {
                $item = $this->dbprefix.$item;
            }
        }

        if ($protectIdentifiers === true && ! inArray($item, $this->_reservedIdentifiers))
        {
            $item = $this->escapeIdentifiers($item);
        }

        return $item.$alias;
    }

    /**
     * Dummy method that allows Query Builder class to be disabled
     * and keep countAll() working.
     *
     * @return void
     */
    protected function _resetSelect()
    {
    }

    public function __get($key)
    {
        return $this->_getParam($key);
    }

    /**
     * Get the param
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function _getParam($key, $default = null)
    {
        $value = isset($this->params[$key]) ? $this->params[$key] : $default;
        return $value;
    }
}
