<?php

namespace LightTest\Database\Adapter;

use Light\Database\Adapter\Adapter;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_filesPath = dirname(__DIR__) . '/_files/';
    }

    public function tearDown()
    {
    }
    
    public function testGetAdapterOfMysqli()
    {
        $params = include($this->_filesPath . 'mysqli.php');

        $adapter = Adapter::getDriver($params['normal']);
        $this->assertInstanceOf('Light\Database\Adapter\Driver\Mysqli\Mysqli', $adapter);

        $adapter = Adapter::getDriver($params['failover']);
        $this->assertInstanceOf('Light\Database\Adapter\Driver\Mysqli\Mysqli', $adapter);

        $this->setExpectedException('\Light\Database\DatabaseException');
        $adapter = Adapter::getDriver($params['error']);
    }

    public function testGetAdapterOfPdoMysql()
    {
        $params = include($this->_filesPath . 'pdo_mysql.php');

        $adapter = Adapter::getDriver($params['normal']);
        $this->assertInstanceOf('Light\Database\Adapter\Driver\Pdo\Mysql\Mysql', $adapter);

        $adapter = Adapter::getDriver($params['failover']);
        $this->assertInstanceOf('Light\Database\Adapter\Driver\Pdo\Mysql\Mysql', $adapter);

        $this->setExpectedException('\Light\Database\DatabaseException');
        $adapter = Adapter::getDriver($params['error']);
    }

}
