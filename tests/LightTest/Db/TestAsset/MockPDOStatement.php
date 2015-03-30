<?php

namespace LightTest\Db\TestAsset;

use PDO;
use PDOStatement;
use Light\Db\ORM;

class MockPDOStatement extends PDOStatement
{
   private $current_row = 0;
   private $statement = null;
   private $bindParams = array();
   
   public function __construct($statement)
   {
       $this->statement = $statement;
   }

   public function execute($params = null)
   {
       $count = 0;
       $m = array();

       if (is_null($params)) {
           $params = $this->bindParams;
       }

       if (preg_match_all('/"[^"\\\\]*(?:\\?)[^"\\\\]*"|\'[^\'\\\\]*(?:\\?)[^\'\\\\]*\'|(\\?)/', $this->statement, $m, PREG_SET_ORDER)) {
           $count = count($m);
           for ($v = 0; $v < $count; $v++) {
               if (count($m[$v]) == 1) unset($m[$v]);
           }
           $count = count($m);
           for ($i = 0; $i < $count; $i++) {
               if (!isset($params[$i])) {
                   ob_start();
                   var_dump($m, $params);
                   $output = ob_get_clean();
                   throw new \Exception('Incorrect parameter count. Expected ' . $count . ' got ' . count($params) . ".\n" . $this->statement . "\n" . $output);
               }
           }
       }
   }

   public function bindParam($index, $value, $type)
   {
       if (!is_int($index)) {
           throw new Exception('Incorrect parameter type. Expected $index to be an integer.');
       }

       if (!is_int($type) || ($type != PDO::PARAM_STR && $type != PDO::PARAM_NULL && $type != PDO::PARAM_BOOL && $type != PDO::PARAM_INT)) {
           throw new Exception('Incorrect parameter type. Expected $type to be an integer.');
       }

       $this->bindParams[$index - 1] = $value;
   }
   
   public function fetch($fetch_style=PDO::FETCH_BOTH, $cursor_orientation=PDO::FETCH_ORI_NEXT, $cursor_offset=0)
   {
       if ($this->current_row == 5) {
           return false;
       } else {
           return array('name' => 'Fred', 'age' => 10, 'id' => ++$this->current_row);
       }
   }
}
