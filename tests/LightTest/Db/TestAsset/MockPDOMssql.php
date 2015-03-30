<?php

namespace LightTest\Db\TestAsset;

use Light\Db\ORM;

class MockMsSqlPDO extends MockPDO {

    public $fake_driver = 'mssql';

    public function getAttribute($attribute)
    {
        if ($attribute == self::ATTR_DRIVER_NAME) {
            if (!is_null($this->fake_driver)) {
                return $this->fake_driver;
            }
        }
        
        return parent::getAttribute($attribute);
    }
}
