<?php

class LogWriterTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $this->expectOutputString('Hello!' . PHP_EOL);
        $handle = fopen('php://output', 'w');
        $fw = new \Light\Logger\Writer($handle);
        $this->assertTrue($fw->write('Hello!') > 0); 
    }

    public function testInstantiationWithNonResource()
    {
        $this->setExpectedException('InvalidArgumentException');
        $fw = new \Light\Logger\Writer(@fopen('/foo/bar.txt', 'w'));
    }
}
