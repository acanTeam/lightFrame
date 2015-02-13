<?php

use Light\View\View;
use Light\Stdlib\Parameters;

class ViewTest extends PHPUnit_Framework_TestCase
{
    public function testGetDataAll()
    {
        $view = new View();
        $prop = new \ReflectionProperty($view, 'datas');
        $prop->setAccessible(true);
        $prop->setValue($view, new Parameters(array('foo' => 'bar')));

        $this->assertSame(array('foo' => 'bar'), $view->getData());
    }

    public function testGetDataKeyExists()
    {
        $view = new View();
        $prop = new \ReflectionProperty($view, 'datas');
        $prop->setAccessible(true);
        $prop->setValue($view, new Parameters(array('foo' => 'bar')));

        $this->assertEquals('bar', $view->getData('foo'));
    }

    public function testGetDataKeyNotExists()
    {
        $view = new View();
        $prop = new \ReflectionProperty($view, 'datas');
        $prop->setAccessible(true);
        $prop->setValue($view, new Parameters(array('foo' => 'bar')));

        $this->assertNull($view->getData('abc'));
    }

    public function testSetDataKeyValue()
    {
        $view = new View();
        $prop = new \ReflectionProperty($view, 'datas');
        $prop->setAccessible(true);
        $view->setData('foo', 'bar');

        $this->assertEquals(array('foo' => 'bar'), $prop->getValue($view)->all());
    }

    public function testSetDataKeyValueAsClosure()
    {
        $view = new View();
        $prop = new \ReflectionProperty($view, 'datas');
        $prop->setAccessible(true);

        $view->setData('fooClosure', function () {
            return 'foo';
        });

        $value = $prop->getValue($view)->get('fooClosure');
        $this->assertInstanceOf('Closure', $value);
        $this->assertEquals('foo', $value());
    }

    public function testSetDataArray()
    {
        $view = new View();
        $prop = new \ReflectionProperty($view, 'datas');
        $prop->setAccessible(true);
        $view->setData(array('foo' => 'bar'));

        $this->assertEquals(array('foo' => 'bar'), $prop->getValue($view)->all());
    }

    public function testSetDataInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException');

        $view = new View();
        $view->setData('foo');
    }

    public function testAppendData()
    {
        $view = new View();
        $prop = new \ReflectionProperty($view, 'datas');
        $prop->setAccessible(true);
        $view->appendData(array('foo' => 'bar'));

        $this->assertEquals(array('foo' => 'bar'), $prop->getValue($view)->all());
    }

    public function testLocalData()
    {
        $view = new View();
        $prop1 = new \ReflectionProperty($view, 'datas');
        $prop1->setAccessible(true);
        $prop1->setValue($view, new Parameters(array('foo' => 'bar')));

        $prop2 = new \ReflectionProperty($view, 'templatePaths');
        $prop2->setAccessible(true);
        $prop2->setValue($view, array(dirname(__FILE__) . '/templates'));

        $output = $view->fetch('test.php', array('foo' => 'baz'));
        $this->assertEquals('test output baz', $output);
    }

    public function testAppendDataOverwrite()
    {
        $view = new View();
        $prop = new \ReflectionProperty($view, 'datas');
        $prop->setAccessible(true);
        $prop->setValue($view, new Parameters(array('foo' => 'bar')));
        $view->appendData(array('foo' => '123'));

        $this->assertEquals(array('foo' => '123'), $prop->getValue($view)->all());
    }

    public function testAppendDataInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException');

        $view = new View();
        $view->appendData('foo');
    }

    public function testGetTemplatePaths()
    {
        $view = new View();
        $property = new \ReflectionProperty($view, 'templatePaths');
        $property->setAccessible(true);
        $property->setValue($view, 'templates');

        $this->assertEquals('templates', $view->getTemplatePaths());
    }

    public function testSetTemplatePaths()
    {
        $view = new View();
        $directory = array('templates');
        $view->setTemplatePaths($directory); // <-- Should strip trailing slash

        $this->assertAttributeEquals($directory, 'templatePaths', $view);
    }

    public function testDisplay()
    {
        $this->expectOutputString('test output bar');

        $view = new View();
        $prop1 = new \ReflectionProperty($view, 'datas');
        $prop1->setAccessible(true);
        $prop1->setValue($view, new Parameters(array('foo' => 'bar')));

        $prop2 = new \ReflectionProperty($view, 'templatePaths');
        $prop2->setAccessible(true);
        $prop2->setValue($view, array(dirname(__FILE__) . '/templates'));

        $view->display('test.php');
    }

    public function testDisplayTemplateThatDoesNotExist()
    {
        $this->setExpectedException('\RuntimeException');

        $view = new View();

        $prop2 = new \ReflectionProperty($view, 'templatePaths');
        $prop2->setAccessible(true);
        $prop2->setValue($view, array(dirname(__FILE__) . '/templates'));

        $view->display('foo.php');
    }
}
