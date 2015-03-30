<?php

namespace LightTest\Db\TestAsset;

use Light\Db\ORM;

class MockPDOOther extends MockPDO
{
    public function prepare($statement, $driver_options = array())
    {
        $this->last_query = new MockPDOStatementOther($statement);
        return $this->last_query;
    }
}
