<?php

class CookiesTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->cookie = new \Light\Http\Cookies();
    }

    public function tearDown()
    {
    }
    
    public function testSetWithStringValue()
    {
        $this->cookie->set('foo', 'bar');
        $expected = array(
            'foo' => array(
                'value' => 'bar',
                'expires' => null,
                'domain' => null,
                'path' => null,
                'secure' => false,
                'httponly' => false
            )
        );
        $this->assertAttributeEquals($expected, 'data', $this->cookie);
    }

    public function testSetWithArrayValue()
    {
        $now = time();
        $this->cookie->set('foo', array(
            'value' => 'bar',
            'expires' => $now + 86400,
            'domain' => '.example.com',
            'path' => '/',
            'secure' => true,
            'httponly' => true
        ));
        $expected = array(
            'foo' => array(
                'value' => 'bar',
                'expires' => $now + 86400,
                'domain' => '.example.com',
                'path' => '/',
                'secure' => true,
                'httponly' => true
            )
        );

        $this->assertAttributeEquals($expected, 'data', $this->cookie);
    }

    public function testRemove()
    {
        $this->cookie->remove('foo');
        $prop = new \ReflectionProperty($this->cookie, 'data');
        $prop->setAccessible(true);
        $cValue = $prop->getValue($this->cookie);
        $this->assertEquals('', $cValue['foo']['value']);
        $this->assertLessThan(time(), $cValue['foo']['expires']);
    }
}
