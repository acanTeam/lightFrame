<?php

class ParametersTest extends PHPUnit_Framework_TestCase
{
    protected $bag;
    protected $property;

    public function setUp()
    {
        $this->bag = new \Light\Stdlib\Parameters();
        $this->property = new \ReflectionProperty($this->bag, 'data');
        $this->property->setAccessible(true);
    }

    public function testSet()
    {
        $this->bag->set('foo', 'bar');
        $this->assertArrayHasKey('foo', $this->property->getValue($this->bag));
        $bag =  $this->property->getValue($this->bag);
        $this->assertEquals('bar', $bag['foo']);
    }

    public function testGet()
    {
        $this->property->setValue($this->bag, array('foo' => 'bar'));
        $this->assertEquals('bar', $this->bag->get('foo'));
    }

    public function testGetNotExists()
    {
        $this->property->setValue($this->bag, array('foo' => 'bar'));
        $this->assertEquals('default', $this->bag->get('abc', 'default'));
    }

    public function testAdd()
    {
        $this->bag->replace(array(
            'abc' => '123',
            'foo' => 'bar'
        ));
        $this->assertArrayHasKey('abc', $this->property->getValue($this->bag));
        $this->assertArrayHasKey('foo', $this->property->getValue($this->bag));
        $bag = $this->property->getValue($this->bag);
        $this->assertEquals('123', $bag['abc']);
        $this->assertEquals('bar', $bag['foo']);
    }

    public function testAll()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        $this->assertEquals($data, $this->bag->all());
    }

    public function testKeys()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        $this->assertEquals(array('abc', 'foo'), $this->bag->keys());
    }

    public function testRemove()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        $this->bag->remove('foo');
        $this->assertEquals(array('abc' => '123'), $this->property->getValue($this->bag));
    }

    public function testClear()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        $this->bag->clear();
        $this->assertEquals(array(), $this->property->getValue($this->bag));
    }

    public function testArrayAccessGet()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        $this->assertEquals('bar', $this->bag['foo']);
    }

    public function testArrayAccessParameters()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        $this->bag['foo'] = 'changed';
        $bag = $this->property->getValue($this->bag);
        $this->assertEquals('changed', $bag['foo']);
    }

    public function testArrayAccessExists()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        $this->assertTrue(isset($this->bag['foo']));
        $this->assertFalse(isset($this->bag['bar']));
    }

    public function testArrayAccessUnset()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        unset($this->bag['foo']);
        $this->assertEquals(array('abc' => '123'), $this->property->getValue($this->bag));
    }

    public function testCount()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        $this->assertEquals(2, count($this->bag));
    }

    public function testGetIterator()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);
        $this->assertInstanceOf('\ArrayIterator', $this->bag->getIterator());
    }

    public function testPropertyOverloadGet()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);

        $this->assertEquals('123', $this->bag->abc);
        $this->assertEquals('bar', $this->bag->foo);
    }

    public function testPropertyOverloadParameters()
    {
        $this->bag->foo = 'bar';
        $this->assertArrayHasKey('foo', $this->property->getValue($this->bag));
        $this->assertEquals('bar', $this->bag->foo);
    }

    public function testPropertyOverloadingIsset()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);

        $this->assertTrue(isset($this->bag->abc));
        $this->assertTrue(isset($this->bag->foo));
        $this->assertFalse(isset($this->bag->foobar));
    }

    public function testPropertyOverloadingUnset()
    {
        $data = array(
            'abc' => '123',
            'foo' => 'bar'
        );
        $this->property->setValue($this->bag, $data);

        $this->assertTrue(isset($this->bag->abc));
        unset($this->bag->abc);
        $this->assertFalse(isset($this->bag->abc));
        $this->assertArrayNotHasKey('abc', $this->property->getValue($this->bag));
        $this->assertArrayHasKey('foo', $this->property->getValue($this->bag));
    }

    public function testProtect()
    {
        $callable = function () {
            return 'foo';
        };
        $result = $this->bag->protect($callable);

        $this->assertInstanceOf('\Closure', $result);
        $this->assertSame($callable, $result());
    }
}
