<?php

namespace LightTest\Db\TestAsset;

use Light\Db\ORM;

class MockPDO extends \PDO
{
   
   public function prepare($statement, $driver_options=array()) {
       $this->last_query = new MockPDOStatement($statement);
       return $this->last_query;
   }
}
