<?php

namespace Light\Db;

use PDO;

class ORM implements \ArrayAccess {

    // ----------------------- //
    // --- CLASS CONSTANTS --- //
    // ----------------------- //

    // WHERE and HAVING condition array keys
    const CONDITION_FRAGMENT = 0;
    const CONDITION_VALUES = 1;

    const DEFAULT_CONNECTION = 'default';

    // Limit clause style
    const LIMIT_STYLE_TOP_N = "top";
    const LIMIT_STYLE_LIMIT = "limit";

    // ------------------------ //
    // --- CLASS PROPERTIES --- //
    // ------------------------ //

    // Class configuration
    protected static $_defaultConfig = array(
        'connectionString' => 'sqlite::memory:',
        'idColumn' => 'id',
        'idColumnOverrides' => array(),
        'errorMode' => PDO::ERRMODE_EXCEPTION,
        'username' => null,
        'password' => null,
        'driverOptions' => null,
        'identifierQuoteCharacter' => null, // if this is null, will be autodetected
        'limitClauseStyle' => null, // if this is null, will be autodetected
        'logging' => false,
        'logger' => null,
        'caching' => false,
        'cachingAutoClear' => false,
        'returnResultSets' => false,
    );

    // Map of configuration settings
    protected static $_config = array();

    // Map of database connections, instances of the PDO class
    protected static $_db = array();

    // Last query run, only populated if logging is enabled
    protected static $_lastQuery;

    // Log of all queries run, mapped by connection key, only populated if logging is enabled
    protected static $_queryLog = array();

    // Query cache, only used if query caching is enabled
    protected static $_queryCache = array();

    // Reference to previously used PDOStatement object to enable low-level access, if needed
    protected static $_lastStatement = null;

    // --------------------------- //
    // --- INSTANCE PROPERTIES --- //
    // --------------------------- //

    // Key name of the connections in self::$_db used by this instance
    protected $_connectionName;

    // The name of the table the current ORM instance is associated with
    protected $_tableName;

    // Alias for the table to be used in SELECT queries
    protected $_tableAlias = null;

    // Values to be bound to the query
    protected $_values = array();

    // Columns to select in the result
    protected $_resultColumns = array('*');

    // Are we using the default result column or have these been manually changed?
    protected $_usingDefaultResultColumns = true;

    // Join sources
    protected $_joinSources = array();

    // Should the query include a DISTINCT keyword?
    protected $_distinct = false;

    // Is this a raw query?
    protected $_isRawQuery = false;

    // The raw query
    protected $_rawQuery = '';

    // The raw query parameters
    protected $_rawParameters = array();

    // Array of WHERE clauses
    protected $_whereConditions = array();

    // LIMIT
    protected $_limit = null;

    // OFFSET
    protected $_offset = null;

    // ORDER BY
    protected $_orderBy = array();

    // GROUP BY
    protected $_groupBy = array();

    // HAVING
    protected $_havingConditions = array();

    // The data for a hydrated instance of the class
    protected $_data = array();

    // Fields that have been modified during the
    // lifetime of the object
    protected $_dirtyFields = array();

    // Fields that are to be inserted in the DB raw
    protected $_exprFields = array();

    // Is this a new object (has create() been called)?
    protected $_isNew = false;

    // Name of the column to use as the primary key for
    // this instance only. Overrides the config settings.
    protected $_instanceIdColumn = null;

    // ---------------------- //
    // --- STATIC METHODS --- //
    // ---------------------- //

    /**
     * Pass configuration settings to the class in the form of
     * key/value pairs. As a shortcut, if the second argument
     * is omitted and the key is a string, the setting is
     * assumed to be the DSN string used by PDO to connect
     * to the database (often, this will be the only configuration
     * required to use Idiorm). If you have more than one setting
     * you wish to configure, another shortcut is to pass an array
     * of settings (and omit the second argument).
     * @param string $key
     * @param mixed $value
     * @param string $connectionName Which connection to use
     */
    public static function configure($key, $value = null, $connectionName = self::DEFAULT_CONNECTION) {
        self::_setupDbConfig($connectionName); //ensures at least default config is set

        if (is_array($key)) {
            // Shortcut: If only one array argument is passed,
            // assume it's an array of configuration settings
            foreach ($key as $confKey => $confValue) {
                self::configure($confKey, $confValue, $connectionName);
            }
        } else {
            if (is_null($value)) {
                // Shortcut: If only one string argument is passed, 
                // assume it's a connection string
                $value = $key;
                $key = 'connectionString';
            }
            self::$_config[$connectionName][$key] = $value;
        }
    }

    /**
     * Retrieve configuration options by key, or as whole array.
     * @param string $key
     * @param string $connectionName Which connection to use
     */
    public static function getConfig($key = null, $connectionName = self::DEFAULT_CONNECTION) {
        if ($key) {
            return self::$_config[$connectionName][$key];
        } else {
            return self::$_config[$connectionName];
        }
    }

    /**
     * Delete all configs in _config array.
     */
    public static function resetConfig() {
        self::$_config = array();
    }
    
    /**
     * Despite its slightly odd name, this is actually the factory
     * method used to acquire instances of the class. It is named
     * this way for the sake of a readable interface, ie
     * ORM::forTable('tableName')->findOne()-> etc. As such,
     * this will normally be the first method called in a chain.
     * @param string $tableName
     * @param string $connectionName Which connection to use
     * @return ORM
     */
    public static function forTable($tableName, $connectionName = self::DEFAULT_CONNECTION) {
        self::_setupDb($connectionName);
        return new self($tableName, array(), $connectionName);
    }

    /**
     * Set up the database connection used by the class
     * @param string $connectionName Which connection to use
     */
    protected static function _setupDb($connectionName = self::DEFAULT_CONNECTION) {
        if (!array_key_exists($connectionName, self::$_db) ||
            !is_object(self::$_db[$connectionName])) {
            self::_setupDbConfig($connectionName);

            $db = new PDO(
                self::$_config[$connectionName]['connectionString'],
                self::$_config[$connectionName]['username'],
                self::$_config[$connectionName]['password'],
                self::$_config[$connectionName]['driverOptions']
            );

            $db->setAttribute(PDO::ATTR_ERRMODE, self::$_config[$connectionName]['errorMode']);
            self::setDb($db, $connectionName);
        }
    }

   /**
    * Ensures configuration (multiple connections) is at least set to default.
    * @param string $connectionName Which connection to use
    */
    protected static function _setupDbConfig($connectionName) {
        if (!array_key_exists($connectionName, self::$_config)) {
            self::$_config[$connectionName] = self::$_defaultConfig;
        }
    }

    /**
     * Set the PDO object used by Idiorm to communicate with the database.
     * This is public in case the ORM should use a ready-instantiated
     * PDO object as its database connection. Accepts an optional string key
     * to identify the connection if multiple connections are used.
     * @param PDO $db
     * @param string $connectionName Which connection to use
     */
    public static function setDb($db, $connectionName = self::DEFAULT_CONNECTION) {
        self::_setupDbConfig($connectionName);
        self::$_db[$connectionName] = $db;
        if(!is_null(self::$_db[$connectionName])) {
            self::_setupIdentifierQuoteCharacter($connectionName);
            self::_setupLimitClauseStyle($connectionName);
        }
    }

    /**
     * Delete all registered PDO objects in _db array.
     */
    public static function resetDb() {
        self::$_db = array();
    }

    /**
     * Detect and initialise the character used to quote identifiers
     * (table names, column names etc). If this has been specified
     * manually using ORM::configure('identifierQuoteCharacter', 'some-char'),
     * this will do nothing.
     * @param string $connectionName Which connection to use
     */
    protected static function _setupIdentifierQuoteCharacter($connectionName) {
        if (is_null(self::$_config[$connectionName]['identifierQuoteCharacter'])) {
            self::$_config[$connectionName]['identifierQuoteCharacter'] =
                self::_detectIdentifierQuoteCharacter($connectionName);
        }
    }

    /**
     * Detect and initialise the limit clause style ("SELECT TOP 5" /
     * "... LIMIT 5"). If this has been specified manually using 
     * ORM::configure('limitClauseStyle', 'top'), this will do nothing.
     * @param string $connectionName Which connection to use
     */
    public static function _setupLimitClauseStyle($connectionName) {
        if (is_null(self::$_config[$connectionName]['limitClauseStyle'])) {
            self::$_config[$connectionName]['limitClauseStyle'] =
                self::_detectLimitClauseStyle($connectionName);
        }
    }

    /**
     * Return the correct character used to quote identifiers (table
     * names, column names etc) by looking at the driver being used by PDO.
     * @param string $connectionName Which connection to use
     * @return string
     */
    protected static function _detectIdentifierQuoteCharacter($connectionName) {
        switch(self::getDb($connectionName)->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'pgsql':
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
            case 'sybase':
            case 'firebird':
                return '"';
            case 'mysql':
            case 'sqlite':
            case 'sqlite2':
            default:
                return '`';
        }
    }

    /**
     * Returns a constant after determining the appropriate limit clause
     * style
     * @param string $connectionName Which connection to use
     * @return string Limit clause style keyword/constant
     */
    protected static function _detectLimitClauseStyle($connectionName) {
        switch(self::getDb($connectionName)->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
                return ORM::LIMIT_STYLE_TOP_N;
            default:
                return ORM::LIMIT_STYLE_LIMIT;
        }
    }

    /**
     * Returns the PDO instance used by the the ORM to communicate with
     * the database. This can be called if any low-level DB access is
     * required outside the class. If multiple connections are used,
     * accepts an optional key name for the connection.
     * @param string $connectionName Which connection to use
     * @return PDO
     */
    public static function getDb($connectionName = self::DEFAULT_CONNECTION) {
        self::_setupDb($connectionName); // required in case this is called before Idiorm is instantiated
        return self::$_db[$connectionName];
    }

    /**
     * Executes a raw query as a wrapper for PDOStatement::execute.
     * Useful for queries that can't be accomplished through Idiorm,
     * particularly those using engine-specific features.
     * @example rawExecute('SELECT `name`, AVG(`order`) FROM `customer` GROUP BY `name` HAVING AVG(`order`) > 10')
     * @example rawExecute('INSERT OR REPLACE INTO `widget` (`id`, `name`) SELECT `id`, `name` FROM `otherTable`')
     * @param string $query The raw SQL query
     * @param array  $parameters Optional bound parameters
     * @param string $connectionName Which connection to use
     * @return bool Success
     */
    public static function rawExecute($query, $parameters = array(), $connectionName = self::DEFAULT_CONNECTION) {
        self::_setupDb($connectionName);
        return self::_execute($query, $parameters, $connectionName);
    }

    /**
     * Returns the PDOStatement instance last used by any connection wrapped by the ORM.
     * Useful for access to PDOStatement::rowCount() or error information
     * @return PDOStatement
     */
    public static function getLastStatement() {
        return self::$_lastStatement;
    }

   /**
    * Internal helper method for executing statments. Logs queries, and
    * stores statement object in ::_lastStatment, accessible publicly
    * through ::getLastStatement()
    * @param string $query
    * @param array $parameters An array of parameters to be bound in to the query
    * @param string $connectionName Which connection to use
    * @return bool Response of PDOStatement::execute()
    */
    protected static function _execute($query, $parameters = array(), $connectionName = self::DEFAULT_CONNECTION) {
        $statement = self::getDb($connectionName)->prepare($query);
        self::$_lastStatement = $statement;
        $time = microtime(true);

        foreach ($parameters as $key => &$param) {
            if (is_null($param)) {
                $type = PDO::PARAM_NULL;
            } else if (isBool($param)) {
                $type = PDO::PARAM_BOOL;
            } else if (isInt($param)) {
                $type = PDO::PARAM_INT;
            } else {
                $type = PDO::PARAM_STR;
            }

            $statement->bindParam(isInt($key) ? ++$key : $key, $param, $type);
        }

        $q = $statement->execute();
        self::_logQuery($query, $parameters, $connectionName, (microtime(true)-$time));

        return $q;
    }

    /**
     * Add a query to the internal query log. Only works if the
     * 'logging' config option is set to true.
     *
     * This works by manually binding the parameters to the query - the
     * query isn't executed like this (PDO normally passes the query and
     * parameters to the database which takes care of the binding) but
     * doing it this way makes the logged queries more readable.
     * @param string $query
     * @param array $parameters An array of parameters to be bound in to the query
     * @param string $connectionName Which connection to use
		 * @param float $queryTime Query time
     * @return bool
     */
    protected static function _logQuery($query, $parameters, $connectionName, $queryTime) {
        // If logging is not enabled, do nothing
        if (!self::$_config[$connectionName]['logging']) {
            return false;
        }

        if (!isset(self::$_queryLog[$connectionName])) {
            self::$_queryLog[$connectionName] = array();
        }

        // Strip out any non-integer indexes from the parameters
        foreach($parameters as $key => $value) {
	            if (!isInt($key)) unset($parameters[$key]);
        }

        if (count($parameters) > 0) {
            // Escape the parameters
            $parameters = array_map(array(self::getDb($connectionName), 'quote'), $parameters);

            // Avoid %format collision for vsprintf
            $query = str_replace("%", "%%", $query);

            // Replace placeholders in the query for vsprintf
            if(false !== strpos($query, "'") || false !== strpos($query, '"')) {
                $query = IdiormString::str_replaceOutsideQuotes("?", "%s", $query);
            } else {
                $query = str_replace("?", "%s", $query);
            }

            // Replace the question marks in the query with the parameters
            $boundQuery = vsprintf($query, $parameters);
        } else {
            $boundQuery = $query;
        }

        self::$_lastQuery = $boundQuery;
        self::$_queryLog[$connectionName][] = $boundQuery;
        
        
        if(isCallable(self::$_config[$connectionName]['logger'])){
            $logger = self::$_config[$connectionName]['logger'];
            $logger($boundQuery, $queryTime);
        }
        
        return true;
    }

    /**
     * Get the last query executed. Only works if the
     * 'logging' config option is set to true. Otherwise
     * this will return null. Returns last query from all connections if
     * no connectionName is specified
     * @param null|string $connectionName Which connection to use
     * @return string
     */
    public static function getLastQuery($connectionName = null) {
        if ($connectionName === null) {
            return self::$_lastQuery;
        }
        if (!isset(self::$_queryLog[$connectionName])) {
            return '';
        }

        return end(self::$_queryLog[$connectionName]);
    }

    /**
     * Get an array containing all the queries run on a
     * specified connection up to now.
     * Only works if the 'logging' config option is
     * set to true. Otherwise, returned array will be empty.
     * @param string $connectionName Which connection to use
     */
    public static function getQueryLog($connectionName = self::DEFAULT_CONNECTION) {
        if (isset(self::$_queryLog[$connectionName])) {
            return self::$_queryLog[$connectionName];
        }
        return array();
    }

    /**
     * Get a list of the available connection names
     * @return array
     */
    public static function getConnectionNames() {
        return arrayKeys(self::$_db);
    }

    // ------------------------ //
    // --- INSTANCE METHODS --- //
    // ------------------------ //

    /**
     * "Private" constructor; shouldn't be called directly.
     * Use the ORM::forTable factory method instead.
     */
    protected function __construct($tableName, $data = array(), $connectionName = self::DEFAULT_CONNECTION) {
        $this->_tableName = $tableName;
        $this->_data = $data;

        $this->_connectionName = $connectionName;
        self::_setupDbConfig($connectionName);
    }

    /**
     * Create a new, empty instance of the class. Used
     * to add a new row to your database. May optionally
     * be passed an associative array of data to populate
     * the instance. If so, all fields will be flagged as
     * dirty so all will be saved to the database when
     * save() is called.
     */
    public function create($data=null) {
        $this->_isNew = true;
        if (!is_null($data)) {
            return $this->hydrate($data)->forceAllDirty();
        }
        return $this;
    }

    /**
     * Specify the ID column to use for this instance or array of instances only.
     * This overrides the idColumn and idColumnOverrides settings.
     *
     * This is mostly useful for libraries built on top of Idiorm, and will
     * not normally be used in manually built queries. If you don't know why
     * you would want to use this, you should probably just ignore it.
     */
    public function useIdColumn($idColumn) {
        $this->_instanceIdColumn = $idColumn;
        return $this;
    }

    /**
     * Create an ORM instance from the given row (an associative
     * array of data fetched from the database)
     */
    protected function _createInstanceFromRow($row) {
        $instance = self::forTable($this->_tableName, $this->_connectionName);
        $instance->useIdColumn($this->_instanceIdColumn);
        $instance->hydrate($row);
        return $instance;
    }

    /**
     * Tell the ORM that you are expecting a single result
     * back from your query, and execute it. Will return
     * a single instance of the ORM class, or false if no
     * rows were returned.
     * As a shortcut, you may supply an ID as a parameter
     * to this method. This will perform a primary key
     * lookup on the table.
     */
    public function findOne($id=null) {
        if (!is_null($id)) {
            $this->whereIdIs($id);
        }
        $this->limit(1);
        $rows = $this->_run();

        if (empty($rows)) {
            return false;
        }

        return $this->_createInstanceFromRow($rows[0]);
    }

    /**
     * Tell the ORM that you are expecting multiple results
     * from your query, and execute it. Will return an array
     * of instances of the ORM class, or an empty array if
     * no rows were returned.
     * @return array|\IdiormResultSet
     */
    public function findMany() {
        if(self::$_config[$this->_connectionName]['returnResultSets']) {
            return $this->findResultSet();
        }
        return $this->_findMany();
    }

    /**
     * Tell the ORM that you are expecting multiple results
     * from your query, and execute it. Will return an array
     * of instances of the ORM class, or an empty array if
     * no rows were returned.
     * @return array
     */
    protected function _findMany() {
        $rows = $this->_run();
        return array_map(array($this, '_createInstanceFromRow'), $rows);
    }

    /**
     * Tell the ORM that you are expecting multiple results
     * from your query, and execute it. Will return a result set object
     * containing instances of the ORM class.
     * @return \IdiormResultSet
     */
    public function findResultSet() {
        return new IdiormResultSet($this->_findMany());
    }

    /**
     * Tell the ORM that you are expecting multiple results
     * from your query, and execute it. Will return an array,
     * or an empty array if no rows were returned.
     * @return array
     */
    public function findArray() {
        return $this->_run(); 
    }

    /**
     * Tell the ORM that you wish to execute a COUNT query.
     * Will return an integer representing the number of
     * rows returned.
     */
    public function count($column = '*') {
        return $this->_callAggregateDbFunction(__FUNCTION__, $column);
    }

    /**
     * Tell the ORM that you wish to execute a MAX query.
     * Will return the max value of the choosen column.
     */
    public function max($column)  {
        return $this->_callAggregateDbFunction(__FUNCTION__, $column);
    }

    /**
     * Tell the ORM that you wish to execute a MIN query.
     * Will return the min value of the choosen column.
     */
    public function min($column)  {
        return $this->_callAggregateDbFunction(__FUNCTION__, $column);
    }

    /**
     * Tell the ORM that you wish to execute a AVG query.
     * Will return the average value of the choosen column.
     */
    public function avg($column)  {
        return $this->_callAggregateDbFunction(__FUNCTION__, $column);
    }

    /**
     * Tell the ORM that you wish to execute a SUM query.
     * Will return the sum of the choosen column.
     */
    public function sum($column)  {
        return $this->_callAggregateDbFunction(__FUNCTION__, $column);
    }

    /**
     * Execute an aggregate query on the current connection.
     * @param string $sqlFunction The aggregate function to call eg. MIN, COUNT, etc
     * @param string $column The column to execute the aggregate query against
     * @return int
     */
    protected function _callAggregateDbFunction($sqlFunction, $column) {
        $alias = strtolower($sqlFunction);
        $sqlFunction = strtoupper($sqlFunction);
        if('*' != $column) {
            $column = $this->_quoteIdentifier($column);
        }
        $resultColumns = $this->_resultColumns;
        $this->_resultColumns = array();
        $this->selectExpr("$sqlFunction($column)", $alias);
        $result = $this->findOne();
        $this->_resultColumns = $resultColumns;

        $returnValue = 0;
        if($result !== false && isset($result->$alias)) {
            if (!isNumeric($result->$alias)) {
                $returnValue = $result->$alias;
            }
            elseif((int) $result->$alias == (float) $result->$alias) {
                $returnValue = (int) $result->$alias;
            } else {
                $returnValue = (float) $result->$alias;
            }
        }
        return $returnValue;
    }

     /**
     * This method can be called to hydrate (populate) this
     * instance of the class from an associative array of data.
     * This will usually be called only from inside the class,
     * but it's public in case you need to call it directly.
     */
    public function hydrate($data=array()) {
        $this->_data = $data;
        return $this;
    }

    /**
     * Force the ORM to flag all the fields in the $data array
     * as "dirty" and therefore update them when save() is called.
     */
    public function forceAllDirty() {
        $this->_dirtyFields = $this->_data;
        return $this;
    }

    /**
     * Perform a raw query. The query can contain placeholders in
     * either named or question mark style. If placeholders are
     * used, the parameters should be an array of values which will
     * be bound to the placeholders in the query. If this method
     * is called, all other query building methods will be ignored.
     */
    public function rawQuery($query, $parameters = array()) {
        $this->_isRawQuery = true;
        $this->_rawQuery = $query;
        $this->_rawParameters = $parameters;
        return $this;
    }

    /**
     * Add an alias for the main table to be used in SELECT queries
     */
    public function tableAlias($alias) {
        $this->_tableAlias = $alias;
        return $this;
    }

    /**
     * Internal method to add an unquoted expression to the set
     * of columns returned by the SELECT query. The second optional
     * argument is the alias to return the expression as.
     */
    protected function _addResultColumn($expr, $alias=null) {
        if (!is_null($alias)) {
            $expr .= " AS " . $this->_quoteIdentifier($alias);
        }

        if ($this->_usingDefaultResultColumns) {
            $this->_resultColumns = array($expr);
            $this->_usingDefaultResultColumns = false;
        } else {
            $this->_resultColumns[] = $expr;
        }
        return $this;
    }

    /**
     * Counts the number of columns that belong to the primary
     * key and their value is null.
     */
    public function countNullIdColumns() {
        if (is_array($this->_getIdColumnName())) {
            return count(arrayFilter($this->id(), 'is_null'));
        } else {
            return is_null($this->id()) ? 1 : 0;
        }
    }

    /**
     * Add a column to the list of columns returned by the SELECT
     * query. This defaults to '*'. The second optional argument is
     * the alias to return the column as.
     */
    public function select($column, $alias=null) {
        $column = $this->_quoteIdentifier($column);
        return $this->_addResultColumn($column, $alias);
    }

    /**
     * Add an unquoted expression to the list of columns returned
     * by the SELECT query. The second optional argument is
     * the alias to return the column as.
     */
    public function selectExpr($expr, $alias=null) {
        return $this->_addResultColumn($expr, $alias);
    }

    /**
     * Add columns to the list of columns returned by the SELECT
     * query. This defaults to '*'. Many columns can be supplied
     * as either an array or as a list of parameters to the method.
     * 
     * Note that the alias must not be numeric - if you want a
     * numeric alias then prepend it with some alpha chars. eg. a1
     * 
     * @example selectMany(array('alias' => 'column', 'column2', 'alias2' => 'column3'), 'column4', 'column5');
     * @example selectMany('column', 'column2', 'column3');
     * @example selectMany(array('column', 'column2', 'column3'), 'column4', 'column5');
     * 
     * @return \ORM
     */
    public function selectMany() {
        $columns = funcGetArgs();
        if(!empty($columns)) {
            $columns = $this->_normaliseSelectManyColumns($columns);
            foreach($columns as $alias => $column) {
                if(isNumeric($alias)) {
                    $alias = null;
                }
                $this->select($column, $alias);
            }
        }
        return $this;
    }

    /**
     * Add an unquoted expression to the list of columns returned
     * by the SELECT query. Many columns can be supplied as either 
     * an array or as a list of parameters to the method.
     * 
     * Note that the alias must not be numeric - if you want a
     * numeric alias then prepend it with some alpha chars. eg. a1
     * 
     * @example selectManyExpr(array('alias' => 'column', 'column2', 'alias2' => 'column3'), 'column4', 'column5')
     * @example selectManyExpr('column', 'column2', 'column3')
     * @example selectManyExpr(array('column', 'column2', 'column3'), 'column4', 'column5')
     * 
     * @return \ORM
     */
    public function selectManyExpr() {
        $columns = funcGetArgs();
        if(!empty($columns)) {
            $columns = $this->_normaliseSelectManyColumns($columns);
            foreach($columns as $alias => $column) {
                if(isNumeric($alias)) {
                    $alias = null;
                }
                $this->selectExpr($column, $alias);
            }
        }
        return $this;
    }

    /**
     * Take a column specification for the select many methods and convert it
     * into a normalised array of columns and aliases.
     * 
     * It is designed to turn the following styles into a normalised array:
     * 
     * array(array('alias' => 'column', 'column2', 'alias2' => 'column3'), 'column4', 'column5'))
     * 
     * @param array $columns
     * @return array
     */
    protected function _normaliseSelectManyColumns($columns) {
        $return = array();
        foreach($columns as $column) {
            if(is_array($column)) {
                foreach($column as $key => $value) {
                    if(!isNumeric($key)) {
                        $return[$key] = $value;
                    } else {
                        $return[] = $value;
                    }
                }
            } else {
                $return[] = $column;
            }
        }
        return $return;
    }

    /**
     * Add a DISTINCT keyword before the list of columns in the SELECT query
     */
    public function distinct() {
        $this->_distinct = true;
        return $this;
    }

    /**
     * Internal method to add a JOIN source to the query.
     *
     * The joinOperator should be one of INNER, LEFT OUTER, CROSS etc - this
     * will be prepended to JOIN.
     *
     * The table should be the name of the table to join to.
     *
     * The constraint may be either a string or an array with three elements. If it
     * is a string, it will be compiled into the query as-is, with no escaping. The
     * recommended way to supply the constraint is as an array with three elements:
     *
     * firstColumn, operator, secondColumn
     *
     * Example: array('user.id', '=', 'profile.userId')
     *
     * will compile to
     *
     * ON `user`.`id` = `profile`.`userId`
     *
     * The final (optional) argument specifies an alias for the joined table.
     */
    protected function _addJoinSource($joinOperator, $table, $constraint, $tableAlias=null) {

        $joinOperator = trim("{$joinOperator} JOIN");

        $table = $this->_quoteIdentifier($table);

        // Add table alias if present
        if (!is_null($tableAlias)) {
            $tableAlias = $this->_quoteIdentifier($tableAlias);
            $table .= " {$tableAlias}";
        }

        // Build the constraint
        if (is_array($constraint)) {
            list($firstColumn, $operator, $secondColumn) = $constraint;
            $firstColumn = $this->_quoteIdentifier($firstColumn);
            $secondColumn = $this->_quoteIdentifier($secondColumn);
            $constraint = "{$firstColumn} {$operator} {$secondColumn}";
        }

        $this->_joinSources[] = "{$joinOperator} {$table} ON {$constraint}";
        return $this;
    }

    /**
     * Add a RAW JOIN source to the query
     */
    public function rawJoin($table, $constraint, $tableAlias, $parameters = array()) {
        // Add table alias if present
        if (!is_null($tableAlias)) {
            $tableAlias = $this->_quoteIdentifier($tableAlias);
            $table .= " {$tableAlias}";
        }

        $this->_values = arrayMerge($this->_values, $parameters);

        // Build the constraint
        if (is_array($constraint)) {
            list($firstColumn, $operator, $secondColumn) = $constraint;
            $firstColumn = $this->_quoteIdentifier($firstColumn);
            $secondColumn = $this->_quoteIdentifier($secondColumn);
            $constraint = "{$firstColumn} {$operator} {$secondColumn}";
        }

        $this->_joinSources[] = "{$table} ON {$constraint}";
        return $this;
    }

    /**
     * Add a simple JOIN source to the query
     */
    public function join($table, $constraint, $tableAlias=null) {
        return $this->_addJoinSource("", $table, $constraint, $tableAlias);
    }

    /**
     * Add an INNER JOIN souce to the query
     */
    public function innerJoin($table, $constraint, $tableAlias=null) {
        return $this->_addJoinSource("INNER", $table, $constraint, $tableAlias);
    }

    /**
     * Add a LEFT OUTER JOIN souce to the query
     */
    public function leftOuterJoin($table, $constraint, $tableAlias=null) {
        return $this->_addJoinSource("LEFT OUTER", $table, $constraint, $tableAlias);
    }

    /**
     * Add an RIGHT OUTER JOIN souce to the query
     */
    public function rightOuterJoin($table, $constraint, $tableAlias=null) {
        return $this->_addJoinSource("RIGHT OUTER", $table, $constraint, $tableAlias);
    }

    /**
     * Add an FULL OUTER JOIN souce to the query
     */
    public function fullOuterJoin($table, $constraint, $tableAlias=null) {
        return $this->_addJoinSource("FULL OUTER", $table, $constraint, $tableAlias);
    }

    /**
     * Internal method to add a HAVING condition to the query
     */
    protected function _addHaving($fragment, $values=array()) {
        return $this->_addCondition('having', $fragment, $values);
    }

    /**
     * Internal method to add a HAVING condition to the query
     */
    protected function _addSimpleHaving($columnName, $separator, $value) {
        return $this->_addSimpleCondition('having', $columnName, $separator, $value);
    }

    /**
     * Internal method to add a HAVING clause with multiple values (like IN and NOT IN)
     */
    public function _addHavingPlaceholder($columnName, $separator, $values) {
        if (!is_array($columnName)) {
            $data = array($columnName => $values);
        } else {
            $data = $columnName;
        }
        $result = $this;
        foreach ($data as $key => $val) {
            $column = $result->_quoteIdentifier($key);
            $placeholders = $result->_createPlaceholders($val);
            $result = $result->_addHaving("{$column} {$separator} ({$placeholders})", $val);    
        }
        return $result;
    }

    /**
     * Internal method to add a HAVING clause with no parameters(like IS NULL and IS NOT NULL)
     */
    public function _addHavingNoValue($columnName, $operator) {
        $conditions = (is_array($columnName)) ? $columnName : array($columnName);
        $result = $this;
        foreach($conditions as $column) {
            $column = $this->_quoteIdentifier($column);
            $result = $result->_addHaving("{$column} {$operator}");
        }
        return $result;
    }

    /**
     * Internal method to add a WHERE condition to the query
     */
    protected function _addWhere($fragment, $values=array()) {
        return $this->_addCondition('where', $fragment, $values);
    }

    /**
     * Internal method to add a WHERE condition to the query
     */
    protected function _addSimpleWhere($columnName, $separator, $value) {
        return $this->_addSimpleCondition('where', $columnName, $separator, $value);
    }

    /**
     * Add a WHERE clause with multiple values (like IN and NOT IN)
     */
    public function _addWherePlaceholder($columnName, $separator, $values) {
        if (!is_array($columnName)) {
            $data = array($columnName => $values);
        } else {
            $data = $columnName;
        }
        $result = $this;
        foreach ($data as $key => $val) {
            $column = $result->_quoteIdentifier($key);
            $placeholders = $result->_createPlaceholders($val);
            $result = $result->_addWhere("{$column} {$separator} ({$placeholders})", $val);    
        }
        return $result;
    }

    /**
     * Add a WHERE clause with no parameters(like IS NULL and IS NOT NULL)
     */
    public function _addWhereNoValue($columnName, $operator) {
        $conditions = (is_array($columnName)) ? $columnName : array($columnName);
        $result = $this;
        foreach($conditions as $column) {
            $column = $this->_quoteIdentifier($column);
            $result = $result->_addWhere("{$column} {$operator}");
        }
        return $result;
    }

    /**
     * Internal method to add a HAVING or WHERE condition to the query
     */
    protected function _addCondition($type, $fragment, $values=array()) {
        $conditionsClassPropertyName = "_{$type}_conditions";
        if (!is_array($values)) {
            $values = array($values);
        }
        array_push($this->$conditionsClassPropertyName, array(
            self::CONDITION_FRAGMENT => $fragment,
            self::CONDITION_VALUES => $values,
        ));
        return $this;
    }

   /**
     * Helper method to compile a simple COLUMN SEPARATOR VALUE
     * style HAVING or WHERE condition into a string and value ready to
     * be passed to the _addCondition method. Avoids duplication
     * of the call to _quoteIdentifier
     *
     * If columnName is an associative array, it will add a condition for each column
     */
    protected function _addSimpleCondition($type, $columnName, $separator, $value) {
        $multiple = is_array($columnName) ? $columnName : array($columnName => $value);
        $result = $this;

        foreach($multiple as $key => $val) {
            // Add the table name in case of ambiguous columns
            if (count($result->_joinSources) > 0 && strpos($key, '.') === false) {
                $table = $result->_tableName;
                if (!is_null($result->_tableAlias)) {
                    $table = $result->_tableAlias;
                }

                $key = "{$table}.{$key}";
            }
            $key = $result->_quoteIdentifier($key);
            $result = $result->_addCondition($type, "{$key} {$separator} ?", $val);
        }
        return $result;
    } 

    /**
     * Return a string containing the given number of question marks,
     * separated by commas. Eg "?, ?, ?"
     */
    protected function _createPlaceholders($fields) {
        if(!empty($fields)) {
            $dbFields = array();
            foreach($fields as $key => $value) {
                // Process expression fields directly into the query
                if(array_key_exists($key, $this->_exprFields)) {
                    $dbFields[] = $value;
                } else {
                    $dbFields[] = '?';
                }
            }
            return implode(', ', $dbFields);
        }
    }
    
    /**
     * Helper method that filters a column/value array returning only those
     * columns that belong to a compound primary key.
     *
     * If the key contains a column that does not exist in the given array,
     * a null value will be returned for it.
     */
    protected function _getCompoundIdColumnValues($value) {
        $filtered = array();
        foreach($this->_getIdColumnName() as $key) {
            $filtered[$key] = isset($value[$key]) ? $value[$key] : null;
        }
        return $filtered;
    }

   /**
     * Helper method that filters an array containing compound column/value
     * arrays.
     */
    protected function _getCompoundIdColumnValuesArray($values) {
        $filtered = array();
        foreach($values as $value) {
            $filtered[] = $this->_getCompoundIdColumnValues($value);
        }
        return $filtered;
    }

    /**
     * Add a WHERE column = value clause to your query. Each time
     * this is called in the chain, an additional WHERE will be
     * added, and these will be ANDed together when the final query
     * is built.
     *
     * If you use an array in $columnName, a new clause will be
     * added for each element. In this case, $value is ignored.
     */
    public function where($columnName, $value=null) {
        return $this->whereEqual($columnName, $value);
    }

    /**
     * More explicitly named version of for the where() method.
     * Can be used if preferred.
     */
    public function whereEqual($columnName, $value=null) {
        return $this->_addSimpleWhere($columnName, '=', $value);
    }

    /**
     * Add a WHERE column != value clause to your query.
     */
    public function whereNotEqual($columnName, $value=null) {
        return $this->_addSimpleWhere($columnName, '!=', $value);
    }

    /**
     * Special method to query the table by its primary key
     *
     * If primary key is compound, only the columns that
     * belong to they key will be used for the query
     */
    public function whereIdIs($id) {
        return (is_array($this->_getIdColumnName())) ?
            $this->where($this->_getCompoundIdColumnValues($id), null) :
            $this->where($this->_getIdColumnName(), $id);
    }

    /**
     * Allows adding a WHERE clause that matches any of the conditions
     * specified in the array. Each element in the associative array will
     * be a different condition, where the key will be the column name.
     *
     * By default, an equal operator will be used against all columns, but
     * it can be overriden for any or every column using the second parameter.
     *
     * Each condition will be ORed together when added to the final query.
     */        
    public function whereAnyIs($values, $operator='=') {
        $data = array();
        $query = array("((");
        $first = true;
        foreach ($values as $item) {
            if ($first) {
                $first = false;
            } else {
                $query[] = ") OR (";
            }
            $firstsub = true;
            foreach($item as $key => $item) {
                $op = isString($operator) ? $operator : (isset($operator[$key]) ? $operator[$key] : '=');
                if ($firstsub) {
                    $firstsub = false;
                } else {
                    $query[] = "AND";
                }
                $query[] = $this->_quoteIdentifier($key);
                $data[] = $item;
                $query[] = $op . " ?";
            }
        }
        $query[] = "))";
        return $this->whereRaw(join($query, ' '), $data);
    }

    /**
     * Similar to whereIdIs() but allowing multiple primary keys.
     *
     * If primary key is compound, only the columns that
     * belong to they key will be used for the query
     */
    public function whereIdIn($ids) {
        return (is_array($this->_getIdColumnName())) ?
            $this->whereAnyIs($this->_getCompoundIdColumnValuesArray($ids)) :
            $this->whereIn($this->_getIdColumnName(), $ids);
    }

    /**
     * Add a WHERE ... LIKE clause to your query.
     */
    public function whereLike($columnName, $value=null) {
        return $this->_addSimpleWhere($columnName, 'LIKE', $value);
    }

    /**
     * Add where WHERE ... NOT LIKE clause to your query.
     */
    public function whereNotLike($columnName, $value=null) {
        return $this->_addSimpleWhere($columnName, 'NOT LIKE', $value);
    }

    /**
     * Add a WHERE ... > clause to your query
     */
    public function whereGt($columnName, $value=null) {
        return $this->_addSimpleWhere($columnName, '>', $value);
    }

    /**
     * Add a WHERE ... < clause to your query
     */
    public function whereLt($columnName, $value=null) {
        return $this->_addSimpleWhere($columnName, '<', $value);
    }

    /**
     * Add a WHERE ... >= clause to your query
     */
    public function whereGte($columnName, $value=null) {
        return $this->_addSimpleWhere($columnName, '>=', $value);
    }

    /**
     * Add a WHERE ... <= clause to your query
     */
    public function whereLte($columnName, $value=null) {
        return $this->_addSimpleWhere($columnName, '<=', $value);
    }

    /**
     * Add a WHERE ... IN clause to your query
     */
    public function whereIn($columnName, $values) {
        return $this->_addWherePlaceholder($columnName, 'IN', $values);
    }

    /**
     * Add a WHERE ... NOT IN clause to your query
     */
    public function whereNotIn($columnName, $values) {
        return $this->_addWherePlaceholder($columnName, 'NOT IN', $values);
    }

    /**
     * Add a WHERE column IS NULL clause to your query
     */
    public function whereNull($columnName) {
        return $this->_addWhereNoValue($columnName, "IS NULL");
    }

    /**
     * Add a WHERE column IS NOT NULL clause to your query
     */
    public function whereNotNull($columnName) {
        return $this->_addWhereNoValue($columnName, "IS NOT NULL");
    }

    /**
     * Add a raw WHERE clause to the query. The clause should
     * contain question mark placeholders, which will be bound
     * to the parameters supplied in the second argument.
     */
    public function whereRaw($clause, $parameters=array()) {
        return $this->_addWhere($clause, $parameters);
    }

    /**
     * Add a LIMIT to the query
     */
    public function limit($limit) {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Add an OFFSET to the query
     */
    public function offset($offset) {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * Add an ORDER BY clause to the query
     */
    protected function _addOrderBy($columnName, $ordering) {
        $columnName = $this->_quoteIdentifier($columnName);
        $this->_orderBy[] = "{$columnName} {$ordering}";
        return $this;
    }

    /**
     * Add an ORDER BY column DESC clause
     */
    public function orderByDesc($columnName) {
        return $this->_addOrderBy($columnName, 'DESC');
    }

    /**
     * Add an ORDER BY column ASC clause
     */
    public function orderByAsc($columnName) {
        return $this->_addOrderBy($columnName, 'ASC');
    }

    /**
     * Add an unquoted expression as an ORDER BY clause
     */
    public function orderByExpr($clause) {
        $this->_orderBy[] = $clause;
        return $this;
    }

    /**
     * Add a column to the list of columns to GROUP BY
     */
    public function groupBy($columnName) {
        $columnName = $this->_quoteIdentifier($columnName);
        $this->_groupBy[] = $columnName;
        return $this;
    }

    /**
     * Add an unquoted expression to the list of columns to GROUP BY 
     */
    public function groupByExpr($expr) {
        $this->_groupBy[] = $expr;
        return $this;
    }

    /**
     * Add a HAVING column = value clause to your query. Each time
     * this is called in the chain, an additional HAVING will be
     * added, and these will be ANDed together when the final query
     * is built.
     *
     * If you use an array in $columnName, a new clause will be
     * added for each element. In this case, $value is ignored.
     */
    public function having($columnName, $value=null) {
        return $this->havingEqual($columnName, $value);
    }

    /**
     * More explicitly named version of for the having() method.
     * Can be used if preferred.
     */
    public function havingEqual($columnName, $value=null) {
        return $this->_addSimpleHaving($columnName, '=', $value);
    }

    /**
     * Add a HAVING column != value clause to your query.
     */
    public function havingNotEqual($columnName, $value=null) {
        return $this->_addSimpleHaving($columnName, '!=', $value);
    }

    /**
     * Special method to query the table by its primary key.
     *
     * If primary key is compound, only the columns that
     * belong to they key will be used for the query
     */
    public function havingIdIs($id) {
        return (is_array($this->_getIdColumnName())) ?
            $this->having($this->_getCompoundIdColumnValues($value)) :
            $this->having($this->_getIdColumnName(), $id);
    }

    /**
     * Add a HAVING ... LIKE clause to your query.
     */
    public function havingLike($columnName, $value=null) {
        return $this->_addSimpleHaving($columnName, 'LIKE', $value);
    }

    /**
     * Add where HAVING ... NOT LIKE clause to your query.
     */
    public function havingNotLike($columnName, $value=null) {
        return $this->_addSimpleHaving($columnName, 'NOT LIKE', $value);
    }

    /**
     * Add a HAVING ... > clause to your query
     */
    public function havingGt($columnName, $value=null) {
        return $this->_addSimpleHaving($columnName, '>', $value);
    }

    /**
     * Add a HAVING ... < clause to your query
     */
    public function havingLt($columnName, $value=null) {
        return $this->_addSimpleHaving($columnName, '<', $value);
    }

    /**
     * Add a HAVING ... >= clause to your query
     */
    public function havingGte($columnName, $value=null) {
        return $this->_addSimpleHaving($columnName, '>=', $value);
    }

    /**
     * Add a HAVING ... <= clause to your query
     */
    public function havingLte($columnName, $value=null) {
        return $this->_addSimpleHaving($columnName, '<=', $value);
    }

    /**
     * Add a HAVING ... IN clause to your query
     */
    public function havingIn($columnName, $values=null) {
        return $this->_addHavingPlaceholder($columnName, 'IN', $values);
    }

    /**
     * Add a HAVING ... NOT IN clause to your query
     */
    public function havingNotIn($columnName, $values=null) {
        return $this->_addHavingPlaceholder($columnName, 'NOT IN', $values);
    }

    /**
     * Add a HAVING column IS NULL clause to your query
     */
    public function havingNull($columnName) {
        return $this->_addHavingNoValue($columnName, 'IS NULL');
    }

    /**
     * Add a HAVING column IS NOT NULL clause to your query
     */
    public function havingNotNull($columnName) {
        return $this->_addHavingNoValue($columnName, 'IS NOT NULL');
    }

    /**
     * Add a raw HAVING clause to the query. The clause should
     * contain question mark placeholders, which will be bound
     * to the parameters supplied in the second argument.
     */
    public function havingRaw($clause, $parameters=array()) {
        return $this->_addHaving($clause, $parameters);
    }

    /**
     * Build a SELECT statement based on the clauses that have
     * been passed to this instance by chaining method calls.
     */
    protected function _buildSelect() {
        // If the query is raw, just set the $this->_values to be
        // the raw query parameters and return the raw query
        if ($this->_isRawQuery) {
            $this->_values = $this->_rawParameters;
            return $this->_rawQuery;
        }

        // Build and return the full SELECT statement by concatenating
        // the results of calling each separate builder method.
        return $this->_joinIfNotEmpty(" ", array(
            $this->_buildSelectStart(),
            $this->_buildJoin(),
            $this->_buildWhere(),
            $this->_buildGroupBy(),
            $this->_buildHaving(),
            $this->_buildOrderBy(),
            $this->_buildLimit(),
            $this->_buildOffset(),
        ));
    }

    /**
     * Build the start of the SELECT statement
     */
    protected function _buildSelectStart() {
        $fragment = 'SELECT ';
        $resultColumns = join(', ', $this->_resultColumns);

        if (!is_null($this->_limit) &&
            self::$_config[$this->_connectionName]['limitClauseStyle'] === ORM::LIMIT_STYLE_TOP_N) {
            $fragment .= "TOP {$this->_limit} ";
        }

        if ($this->_distinct) {
            $resultColumns = 'DISTINCT ' . $resultColumns;
        }

        $fragment .= "{$resultColumns} FROM " . $this->_quoteIdentifier($this->_tableName);

        if (!is_null($this->_tableAlias)) {
            $fragment .= " " . $this->_quoteIdentifier($this->_tableAlias);
        }
        return $fragment;
    }

    /**
     * Build the JOIN sources
     */
    protected function _buildJoin() {
        if (count($this->_joinSources) === 0) {
            return '';
        }

        return join(" ", $this->_joinSources);
    }

    /**
     * Build the WHERE clause(s)
     */
    protected function _buildWhere() {
        return $this->_buildConditions('where');
    }

    /**
     * Build the HAVING clause(s)
     */
    protected function _buildHaving() {
        return $this->_buildConditions('having');
    }

    /**
     * Build GROUP BY
     */
    protected function _buildGroupBy() {
        if (count($this->_groupBy) === 0) {
            return '';
        }
        return "GROUP BY " . join(", ", $this->_groupBy);
    }

    /**
     * Build a WHERE or HAVING clause
     * @param string $type
     * @return string
     */
    protected function _buildConditions($type) {
        $conditionsClassPropertyName = "_{$type}_conditions";
        // If there are no clauses, return empty string
        if (count($this->$conditionsClassPropertyName) === 0) {
            return '';
        }

        $conditions = array();
        foreach ($this->$conditionsClassPropertyName as $condition) {
            $conditions[] = $condition[self::CONDITION_FRAGMENT];
            $this->_values = arrayMerge($this->_values, $condition[self::CONDITION_VALUES]);
        }

        return strtoupper($type) . " " . join(" AND ", $conditions);
    }

    /**
     * Build ORDER BY
     */
    protected function _buildOrderBy() {
        if (count($this->_orderBy) === 0) {
            return '';
        }
        return "ORDER BY " . join(", ", $this->_orderBy);
    }

    /**
     * Build LIMIT
     */
    protected function _buildLimit() {
        $fragment = '';
        if (!is_null($this->_limit) &&
            self::$_config[$this->_connectionName]['limitClauseStyle'] == ORM::LIMIT_STYLE_LIMIT) {
            if (self::getDb($this->_connectionName)->getAttribute(PDO::ATTR_DRIVER_NAME) == 'firebird') {
                $fragment = 'ROWS';
            } else {
                $fragment = 'LIMIT';
            }
            $fragment .= " {$this->_limit}";
        }
        return $fragment;
    }

    /**
     * Build OFFSET
     */
    protected function _buildOffset() {
        if (!is_null($this->_offset)) {
            $clause = 'OFFSET';
            if (self::getDb($this->_connectionName)->getAttribute(PDO::ATTR_DRIVER_NAME) == 'firebird') {
                $clause = 'TO';
            }
            return "$clause " . $this->_offset;
        }
        return '';
    }

    /**
     * Wrapper around PHP's join function which
     * only adds the pieces if they are not empty.
     */
    protected function _joinIfNotEmpty($glue, $pieces) {
        $filteredPieces = array();
        foreach ($pieces as $piece) {
            if (isString($piece)) {
                $piece = trim($piece);
            }
            if (!empty($piece)) {
                $filteredPieces[] = $piece;
            }
        }
        return join($glue, $filteredPieces);
    }

    /**
     * Quote a string that is used as an identifier
     * (table names, column names etc). This method can
     * also deal with dot-separated identifiers eg table.column
     */
    protected function _quoteOneIdentifier($identifier) {
        $parts = explode('.', $identifier);
        $parts = array_map(array($this, '_quoteIdentifierPart'), $parts);
        return join('.', $parts);
    }

    /**
     * Quote a string that is used as an identifier
     * (table names, column names etc) or an array containing
     * multiple identifiers. This method can also deal with
     * dot-separated identifiers eg table.column
     */
    protected function _quoteIdentifier($identifier) {
        if (is_array($identifier)) {
            $result = array_map(array($this, '_quoteOneIdentifier'), $identifier);
            return join(', ', $result);
        } else {
            return $this->_quoteOneIdentifier($identifier);
        }
    }

    /**
     * This method performs the actual quoting of a single
     * part of an identifier, using the identifier quote
     * character specified in the config (or autodetected).
     */
    protected function _quoteIdentifierPart($part) {
        if ($part === '*') {
            return $part;
        }

        $quoteCharacter = self::$_config[$this->_connectionName]['identifierQuoteCharacter'];
        // double up any identifier quotes to escape them
        return $quoteCharacter .
               str_replace($quoteCharacter,
                           $quoteCharacter . $quoteCharacter,
                           $part
               ) . $quoteCharacter;
    }

    /**
     * Create a cache key for the given query and parameters.
     */
    protected static function _createCacheKey($query, $parameters, $tableName = null, $connectionName = self::DEFAULT_CONNECTION) {
        if(isset(self::$_config[$connectionName]['createCacheKey']) and isCallable(self::$_config[$connectionName]['createCacheKey'])){
            return callUserFuncArray(self::$_config[$connectionName]['createCacheKey'], array($query, $parameters, $tableName, $connectionName));
        }
        $parameterString = join(',', $parameters);
        $key = $query . ':' . $parameterString;
        return sha1($key);
    }

    /**
     * Check the query cache for the given cache key. If a value
     * is cached for the key, return the value. Otherwise, return false.
     */
    protected static function _checkQueryCache($cacheKey, $tableName = null, $connectionName = self::DEFAULT_CONNECTION) {
        if(isset(self::$_config[$connectionName]['checkQueryCache']) and isCallable(self::$_config[$connectionName]['checkQueryCache'])){
            return callUserFuncArray(self::$_config[$connectionName]['checkQueryCache'], array($cacheKey, $tableName, $connectionName));
        } elseif (isset(self::$_queryCache[$connectionName][$cacheKey])) {
            return self::$_queryCache[$connectionName][$cacheKey];
        }
        return false;
    }

    /**
     * Clear the query cache
     */
    public static function clearCache($tableName = null, $connectionName = self::DEFAULT_CONNECTION) {
        self::$_queryCache = array();
        if(isset(self::$_config[$connectionName]['clearCache']) and isCallable(self::$_config[$connectionName]['clearCache'])){
            return callUserFuncArray(self::$_config[$connectionName]['clearCache'], array($tableName, $connectionName));
        }
    }

    /**
     * Add the given value to the query cache.
     */
    protected static function _cacheQueryResult($cacheKey, $value, $tableName = null, $connectionName = self::DEFAULT_CONNECTION) {
        if(isset(self::$_config[$connectionName]['cacheQueryResult']) and isCallable(self::$_config[$connectionName]['cacheQueryResult'])){
            return callUserFuncArray(self::$_config[$connectionName]['cacheQueryResult'], array($cacheKey, $value, $tableName, $connectionName));
        } elseif (!isset(self::$_queryCache[$connectionName])) {
            self::$_queryCache[$connectionName] = array();
        }
        self::$_queryCache[$connectionName][$cacheKey] = $value;
    }

    /**
     * Execute the SELECT query that has been built up by chaining methods
     * on this class. Return an array of rows as associative arrays.
     */
    protected function _run() {
        $query = $this->_buildSelect();
        $cachingEnabled = self::$_config[$this->_connectionName]['caching'];

        if ($cachingEnabled) {
            $cacheKey = self::_createCacheKey($query, $this->_values, $this->_tableName, $this->_connectionName);
            $cachedResult = self::_checkQueryCache($cacheKey, $this->_tableName, $this->_connectionName);

            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }

        self::_execute($query, $this->_values, $this->_connectionName);
        $statement = self::getLastStatement();

        $rows = array();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }

        if ($cachingEnabled) {
            self::_cacheQueryResult($cacheKey, $rows, $this->_tableName, $this->_connectionName);
        }

        // reset Idiorm after executing the query
        $this->_values = array();
        $this->_resultColumns = array('*');
        $this->_usingDefaultResultColumns = true;

        return $rows;
    }

    /**
     * Return the raw data wrapped by this ORM
     * instance as an associative array. Column
     * names may optionally be supplied as arguments,
     * if so, only those keys will be returned.
     */
    public function asArray() {
        if (funcNumArgs() === 0) {
            return $this->_data;
        }
        $args = funcGetArgs();
        return arrayIntersectKey($this->_data, arrayFlip($args));
    }

    /**
     * Return the value of a property of this object (database row)
     * or null if not present.
     *
     * If a column-names array is passed, it will return a associative array
     * with the value of each column or null if it is not present.
     */
    public function get($key) {
        if (is_array($key)) {
            $result = array();
            foreach($key as $column) {
                $result[$column] = isset($this->_data[$column]) ? $this->_data[$column] : null;
            }
            return $result;
        } else {
            return isset($this->_data[$key]) ? $this->_data[$key] : null;
        }
    }

    /**
     * Return the name of the column in the database table which contains
     * the primary key ID of the row.
     */
    protected function _getIdColumnName() {
        if (!is_null($this->_instanceIdColumn)) {
            return $this->_instanceIdColumn;
        }
        if (isset(self::$_config[$this->_connectionName]['idColumnOverrides'][$this->_tableName])) {
            return self::$_config[$this->_connectionName]['idColumnOverrides'][$this->_tableName];
        }
        return self::$_config[$this->_connectionName]['idColumn'];
    }

    /**
     * Get the primary key ID of this object.
     */
    public function id($disallowNull = false) {
        $id = $this->get($this->_getIdColumnName());

        if ($disallowNull) {
            if (is_array($id)) {
                foreach ($id as $idPart) {
                    if ($idPart === null) {
                        throw new Exception('Primary key ID contains null value(s)');
                    }
                }
            } else if ($id === null) {
                throw new Exception('Primary key ID missing from row or is null');
            }
        }

        return $id;
    }

    /**
     * Set a property to a particular value on this object.
     * To set multiple properties at once, pass an associative array
     * as the first parameter and leave out the second parameter.
     * Flags the properties as 'dirty' so they will be saved to the
     * database when save() is called.
     */
    public function set($key, $value = null) {
        return $this->_setOrmProperty($key, $value);
    }

    /**
     * Set a property to a particular value on this object.
     * To set multiple properties at once, pass an associative array
     * as the first parameter and leave out the second parameter.
     * Flags the properties as 'dirty' so they will be saved to the
     * database when save() is called. 
     * @param string|array $key
     * @param string|null $value
     */
    public function setExpr($key, $value = null) {
        return $this->_setOrmProperty($key, $value, true);
    }

    /**
     * Set a property on the ORM object.
     * @param string|array $key
     * @param string|null $value
     * @param bool $raw Whether this value should be treated as raw or not
     */
    protected function _setOrmProperty($key, $value = null, $expr = false) {
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $field => $value) {
            $this->_data[$field] = $value;
            $this->_dirtyFields[$field] = $value;
            if (false === $expr and isset($this->_exprFields[$field])) {
                unset($this->_exprFields[$field]);
            } else if (true === $expr) {
                $this->_exprFields[$field] = true;
            }
        }
        return $this;
    }

    /**
     * Check whether the given field has been changed since this
     * object was saved.
     */
    public function isDirty($key) {
        return isset($this->_dirtyFields[$key]);
    }

    /**
     * Check whether the model was the result of a call to create() or not
     * @return bool
     */
    public function isNew() {
        return $this->_isNew;
    }

    /**
     * Save any fields which have been modified on this object
     * to the database.
     */
    public function save() {
        $query = array();

        // remove any expression fields as they are already baked into the query
        $values = arrayValues(arrayDiffKey($this->_dirtyFields, $this->_exprFields));

        if (!$this->_isNew) { // UPDATE
            // If there are no dirty values, do nothing
            if (empty($values) && empty($this->_exprFields)) {
                return true;
            }
            $query = $this->_buildUpdate();
            $id = $this->id(true);
            if (is_array($id)) {
                $values = arrayMerge($values, arrayValues($id));
            } else {
                $values[] = $id;
            }
        } else { // INSERT
            $query = $this->_buildInsert();
        }

        $success = self::_execute($query, $values, $this->_connectionName);
        $cachingAutoClearEnabled = self::$_config[$this->_connectionName]['cachingAutoClear'];
        if($cachingAutoClearEnabled){
            self::clearCache($this->_tableName, $this->_connectionName);
        }
        // If we've just inserted a new record, set the ID of this object
        if ($this->_isNew) {
            $this->_isNew = false;
            if ($this->countNullIdColumns() != 0) {
                $db = self::getDb($this->_connectionName);
                if($db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
                    // it may return several columns if a compound primary
                    // key is used
                    $row = self::getLastStatement()->fetch(PDO::FETCH_ASSOC);
                    foreach($row as $key => $value) {
                        $this->_data[$key] = $value;
                    }
                } else {
                    $column = $this->_getIdColumnName();
                    // if the primary key is compound, assign the last inserted id
                    // to the first column
                    if (is_array($column)) {
                        $column = arraySlice($column, 0, 1);
                    }
                    $this->_data[$column] = $db->lastInsertId();
                }
            }
        }

        $this->_dirtyFields = $this->_exprFields = array();
        return $success;
    }

    /**
     * Add a WHERE clause for every column that belongs to the primary key
     */
    public function _addIdColumnConditions(&$query) {
        $query[] = "WHERE";
        $keys = is_array($this->_getIdColumnName()) ? $this->_getIdColumnName() : array( $this->_getIdColumnName() );
        $first = true;
        foreach($keys as $key) {
            if ($first) {
                $first = false;
            }
            else {
                $query[] = "AND";
            }
            $query[] = $this->_quoteIdentifier($key);
            $query[] = "= ?";
        }
    }

    /**
     * Build an UPDATE query
     */
    protected function _buildUpdate() {
        $query = array();
        $query[] = "UPDATE {$this->_quoteIdentifier($this->_tableName)} SET";

        $fieldList = array();
        foreach ($this->_dirtyFields as $key => $value) {
            if(!array_key_exists($key, $this->_exprFields)) {
                $value = '?';
            }
            $fieldList[] = "{$this->_quoteIdentifier($key)} = $value";
        }
        $query[] = join(", ", $fieldList);
        $this->_addIdColumnConditions($query);
        return join(" ", $query);
    }

    /**
     * Build an INSERT query
     */
    protected function _buildInsert() {
        $query[] = "INSERT INTO";
        $query[] = $this->_quoteIdentifier($this->_tableName);
        $fieldList = array_map(array($this, '_quoteIdentifier'), arrayKeys($this->_dirtyFields));
        $query[] = "(" . join(", ", $fieldList) . ")";
        $query[] = "VALUES";

        $placeholders = $this->_createPlaceholders($this->_dirtyFields);
        $query[] = "({$placeholders})";

        if (self::getDb($this->_connectionName)->getAttribute(PDO::ATTR_DRIVER_NAME) == 'pgsql') {
            $query[] = 'RETURNING ' . $this->_quoteIdentifier($this->_getIdColumnName());
        }

        return join(" ", $query);
    }

    /**
     * Delete this record from the database
     */
    public function delete() {
        $query = array(
            "DELETE FROM",
            $this->_quoteIdentifier($this->_tableName)
        );
        $this->_addIdColumnConditions($query);
        return self::_execute(join(" ", $query), is_array($this->id(true)) ? arrayValues($this->id(true)) : array($this->id(true)), $this->_connectionName);
    }

    /**
     * Delete many records from the database
     */
    public function deleteMany() {
        // Build and return the full DELETE statement by concatenating
        // the results of calling each separate builder method.
        $query = $this->_joinIfNotEmpty(" ", array(
            "DELETE FROM",
            $this->_quoteIdentifier($this->_tableName),
            $this->_buildWhere(),
        ));

        return self::_execute($query, $this->_values, $this->_connectionName);
    }

    // --------------------- //
    // ---  ArrayAccess  --- //
    // --------------------- //

    public function offsetExists($key) {
        return array_key_exists($key, $this->_data);
    }

    public function offsetGet($key) {
        return $this->get($key);
    }

    public function offsetSet($key, $value) {
        if(is_null($key)) {
            throw new InvalidArgumentException('You must specify a key/array index.');
        }
        $this->set($key, $value);
    }

    public function offsetUnset($key) {
        unset($this->_data[$key]);
        unset($this->_dirtyFields[$key]);
    }

    // --------------------- //
    // --- MAGIC METHODS --- //
    // --------------------- //
    public function __get($key) {
        return $this->offsetGet($key);
    }

    public function __set($key, $value) {
        $this->offsetSet($key, $value);
    }

    public function __unset($key) {
        $this->offsetUnset($key);
    }


    public function __isset($key) {
        return $this->offsetExists($key);
    }

    /**
     * Magic method to capture calls to undefined class methods.
     * In this case we are attempting to convert camel case formatted 
     * methods into underscore formatted methods.
     *
     * This allows us to call ORM methods using camel case and remain 
     * backwards compatible.
     * 
     * @param  string   $name
     * @param  array    $arguments
     * @return ORM
     */
    public function __call($name, $arguments)
    {
        $method = strtolower(pregReplace('/([a-z])([A-Z])/', '$1_$2', $name));

        if (methodExists($this, $method)) {
            return callUserFuncArray(array($this, $method), $arguments);
        } else {
            throw new IdiormMethodMissingException("Method $name() does not exist in class " . getClass($this));
        }
    }

    /**
     * Magic method to capture calls to undefined static class methods. 
     * In this case we are attempting to convert camel case formatted 
     * methods into underscore formatted methods.
     *
     * This allows us to call ORM methods using camel case and remain 
     * backwards compatible.
     * 
     * @param  string   $name
     * @param  array    $arguments
     * @return ORM
     */
    public static function __callStatic($name, $arguments)
    {
        $method = strtolower(pregReplace('/([a-z])([A-Z])/', '$1_$2', $name));

        return callUserFuncArray(array('ORM', $method), $arguments);
    }
}
