<?php

namespace Light\Database\Adapter;

use Light\Database\DatabaseException;

class Adapter
{
    public static function getDriver($params = '')
    {
        $params = self::_formatParams($params);
        $driver = self::_createDriver($params);

        return $driver;
    }

    private static function _formatParams($driver)//, $query_builder_override = NULL)
    {
        $params = is_array($driver) ? $driver : array();

        if (is_string($driver)) {
            $infos = @parse_url($driver);
            if (!is_array($infos) || !isset($infos['scheme']) || empty($info['scheme'])) {
                throw DatabaseException('Wrong dsn');
            }
    
            $params = array(
                'dbdriver'    => $dsn['scheme'],
                'hostname'    => isset($dsn['host']) ? rawurldecode($dsn['host']) : '',
                'port'        => isset($dsn['port']) ? rawurldecode($dsn['port']) : '',
                'username'    => isset($dsn['user']) ? rawurldecode($dsn['user']) : '',
                'password'    => isset($dsn['pass']) ? rawurldecode($dsn['pass']) : '',
                'database'    => isset($dsn['path']) ? rawurldecode(substr($dsn['path'], 1)) : ''
            );
    
            if (isset($dsn['query'])) {
                parse_str($dsn['query'], $extra);
    
                foreach ($extra as $key => $val) {
                    if (is_string($val) && in_array(strtoupper($val), array('TRUE', 'FALSE', 'NULL'))) {
                        $val = var_export($val, TRUE);
                    }
    
                    $params[$key] = $val;
                }
            }
        }

        if (empty($params) || !isset($params['dbdriver']) || empty($params['dbdriver'])) {
            throw new DatabaseException('No dbdriver!');
        }

        return $params;
    }

    public static function _createDriver($params)
    {
        if (strtolower($params['dbdriver']) == 'pdo') {
            if (!isset($params['subdriver']) || empty($params['subdriver'])) {
                throw new DatabaseException('No sub driver for pdo');
            }

            $subdriver = ucfirst(strtolower($params['subdriver']));
            $driverClass = "\Light\Database\Adapter\Driver\Pdo\\{$subdriver}\\{$subdriver}";
        } else {
            $driver = ucfirst(strtolower($params['dbdriver']));
            $driverClass = '\Light\Database\Adapter\Driver\\' . $driver . '\\' . $driver;
        }

        if (!class_exists($driverClass)) {
//            throw new DatabaseException("Class '{$driverClass}' not exists");
        }
    
        $adapter = new $driverClass($params);
        return $adapter;
        //$DB->initialize();
        return $DB;
    }
}
